import sys
from PyQt6.QtWidgets import QApplication
from app import ChatApp

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Usage: python main.py <username>")
        sys.exit(1)

    username = sys.argv[1]

    app = QApplication(sys.argv)
    chat_app = ChatApp(username)
    chat_app.show()
    app.exec()