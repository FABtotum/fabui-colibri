from picamera import PiCamera

pc = PiCamera()
pc.rotation = 270
pc.start_preview()

raw_input("Press Enter to continue...")
