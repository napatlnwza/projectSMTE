import cv2
from ultralytics import YOLO
import serial
import time
import requests
import uuid
import qrcode
import numpy as np

# ─── CONFIG ──────────────────────────────────────────────
ARDUINO_PORT        = 'COM3'
POINT_NAME          = 'point A'
BASE_URL            = 'http://localhost/projectsmte/projectSMTE'
API_SCORE_URL       = f'{BASE_URL}/api_bottle.php'
API_SESSION_URL     = f'{BASE_URL}/api_session.php'
SESSION_FORM_URL    = f'{BASE_URL}/session_form.php'
BOTTLES_PER_SCORE   = 3
SCORE_ADD           = 1
SERVO_OPEN_DURATION = 3
COUNT_COOLDOWN      = 4
POLL_INTERVAL       = 1.5    # วินาที — poll รหัสนักเรียนทุกกี่วิ
# ─────────────────────────────────────────────────────────

def make_qr(url, size=300):
    """สร้าง QR Code แล้วแปลงเป็น numpy array สำหรับ OpenCV"""
    qr = qrcode.QRCode(box_size=6, border=2)
    qr.add_data(url)
    qr.make(fit=True)
    img_pil = qr.make_image(fill_color="black", back_color="white").convert("RGB")
    img_np  = np.array(img_pil)
    img_bgr = cv2.cvtColor(img_np, cv2.COLOR_RGB2BGR)
    return cv2.resize(img_bgr, (size, size))

def overlay_qr(frame, qr_img, margin=10):
    """วาง QR Code มุมบนขวาของ frame"""
    h, w   = frame.shape[:2]
    qh, qw = qr_img.shape[:2]
    x = w - qw - margin
    y = margin
    # กรอบขาว
    cv2.rectangle(frame, (x-4, y-4), (x+qw+4, y+qh+4), (255,255,255), -1)
    frame[y:y+qh, x:x+qw] = qr_img
    return frame

# ── เชื่อมต่อ Arduino ──
try:
    arduino = serial.Serial(ARDUINO_PORT, 9600, timeout=1)
    time.sleep(2)
    print("เชื่อมต่อบอร์ดสำเร็จ!")
except:
    print("หาบอร์ดไม่เจอ!")
    arduino = None

model = YOLO("bestlast.pt")
cap   = cv2.VideoCapture(1)

# ── สร้าง Session ใหม่ + QR ──
def new_session():
    sid = str(uuid.uuid4())[:8]   # session id สั้นๆ
    url = f"{SESSION_FORM_URL}?session={sid}"
    qr  = make_qr(url)
    print(f"\n[QR] Session: {sid}")
    print(f"[QR] URL: {url}")
    return sid, qr

session_id, qr_img     = new_session()
current_student_id     = None
servo_is_open          = False
servo_open_time        = 0
bottle_count           = 0
last_bottle_time       = 0
last_poll_time         = 0

print(f"[INFO] จุดทิ้ง: {POINT_NAME} | ครบ {BOTTLES_PER_SCORE} ขวด = +{SCORE_ADD} คะแนน")
print("[INFO] รอนักเรียนสแกน QR Code...")
print("-" * 45)

while cap.isOpened():
    success, frame = cap.read()
    if not success:
        break

    now = time.time()

    # ── Poll รหัสนักเรียนจากเว็บ (ทุก POLL_INTERVAL วิ) ──
    if current_student_id is None and (now - last_poll_time) >= POLL_INTERVAL:
        last_poll_time = now
        try:
            res  = requests.get(f"{API_SESSION_URL}?session={session_id}", timeout=2)
            data = res.json()
            if data.get('found'):
                current_student_id = data['student_id']
                bottle_count       = 0
                last_bottle_time   = 0
                print(f"\n[OK] นักเรียน: {current_student_id} เริ่มหย่อนขวดได้!")
                # ลบ session หลังได้รหัสแล้ว
                requests.delete(f"{API_SESSION_URL}?session={session_id}", timeout=2)
        except:
            pass

    # ── รัน AI ──
    results      = model(frame, conf=0.25)
    found_bottle = False
    found_label  = False

    for result in results:
        for box in result.boxes:
            class_id   = int(box.cls[0])
            class_name = model.names[class_id]
            if class_name == 'bottle':
                found_bottle = True
            elif class_name == 'label':
                found_label = True

    annotated_frame = results[0].plot()

    # ── เจอขวด + ฉลาก ──
    if found_bottle and found_label:

        # เปิด Servo
        if arduino and not servo_is_open:
            arduino.write(b'1')
            servo_is_open = True
            print("เปิด Servo!")
        servo_open_time = now

        # นับขวด (เฉพาะเมื่อมีนักเรียนแล้ว)
        if current_student_id and (now - last_bottle_time) >= COUNT_COOLDOWN:
            bottle_count    += 1
            last_bottle_time = now
            print(f"[นับ] ขวดที่ {bottle_count}/{BOTTLES_PER_SCORE} (ID: {current_student_id})")

            # ── ครบ 3 ขวด → ส่งคะแนน ──
            if bottle_count >= BOTTLES_PER_SCORE:
                print(f"[API] ครบ {BOTTLES_PER_SCORE} ขวด! กำลังส่งคะแนน...")
                try:
                    res  = requests.post(API_SCORE_URL, data={
                        'student_id' : current_student_id,
                        'point_name' : POINT_NAME,
                        'bottles'    : BOTTLES_PER_SCORE,
                        'score_add'  : SCORE_ADD
                    }, timeout=5)
                    data = res.json()
                    if data['success']:
                        print(f"[OK] {data['details']}")
                        print(f"[OK] คะแนน {data['old_score']} -> {data['new_score']}")
                    else:
                        print(f"[ERROR] {data['message']}")
                except Exception as e:
                    print(f"[ERROR] ส่ง API ไม่ได้: {e}")

                # รีเซ็ต → สร้าง QR ใหม่รอคนถัดไป
                current_student_id = None
                bottle_count       = 0
                session_id, qr_img = new_session()
                print("[INFO] QR ใหม่พร้อมแล้ว รอนักเรียนคนถัดไป...")

    # ── ไม่เจอ → ปิด Servo หลัง 3 วิ ──
    else:
        if servo_is_open and (now - servo_open_time) >= SERVO_OPEN_DURATION:
            if arduino:
                arduino.write(b'0')
            servo_is_open = False
            print("ปิด Servo")

    # ── วาง QR บน frame ──
    annotated_frame = overlay_qr(annotated_frame, qr_img)

    # ── แสดงข้อมูลบนหน้าจอ ──
    if current_student_id:
        cv2.putText(annotated_frame,
                    str(f"ID: {current_student_id}  Bottles: {bottle_count}/{BOTTLES_PER_SCORE}"),
                    (10, 35), cv2.FONT_HERSHEY_SIMPLEX, 0.8, (0, 255, 255), 2)
    else:
        cv2.putText(annotated_frame,
                    str("Scan QR to Start"),
                    (10, 35), cv2.FONT_HERSHEY_SIMPLEX, 0.9, (0, 165, 255), 2)

    cv2.imshow("AI Trash Bin", annotated_frame)

    if cv2.waitKey(1) & 0xFF == ord('q'):
        break

cap.release()
if arduino:
    arduino.write(b'0')
    arduino.close()
cv2.destroyAllWindows()