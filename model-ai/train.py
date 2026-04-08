from ultralytics import YOLO

def main():
    # โหลดโมเดล YOLOv8 ตัวเล็กและเร็วที่สุด (Nano) มาเป็นฐาน
    model = YOLO('yolov8n.pt') 

    # เริ่มกระบวนการ Train AI ด้วยไฟล์ data.yaml ของเรา
    print("🚀 กำลังเริ่มกระบวนการ Train AI...")
    results = model.train(data='data.yaml', epochs=50, imgsz=640)

if __name__ == '__main__':
    main()