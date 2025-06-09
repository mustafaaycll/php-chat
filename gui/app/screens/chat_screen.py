"""
ChatScreen window.

- Displays messages for given chat_id
- Allows user to send new message
- Updates when messages_sent event is received
"""

from pathlib import Path
from datetime import datetime

from PyQt6.uic.load_ui import loadUi
from PyQt6.QtWidgets import (
    QMainWindow, QListWidget, QLineEdit,
    QPushButton, QListWidgetItem
)

from ..services import ApiService

class ChatScreen(QMainWindow):
    def __init__(self, username: str, api: ApiService, chat_id: int):
        """
        Initialize ChatScreen window.
        Loads chat messages initially.
        """
        super().__init__()
        loadUi(
            str(Path(__file__).resolve().parent.parent / "ui" / "chat_interface.ui"),
            self
        )
        self.username = username
        self.api = api
        self.chat_id = chat_id

        # UI elements
        self.messages_list_widget: QListWidget = self.findChild(QListWidget, "messages_list_widget")
        self.message_line_edit: QLineEdit      = self.findChild(QLineEdit, "message_line_edit")
        self.send_push_button: QPushButton     = self.findChild(QPushButton, "send_push_button")

        # Initially disable send button
        self.send_push_button.setEnabled(False)

        # Wire UI callbacks
        self.message_line_edit.textChanged.connect(self._on_text_changed)
        self.send_push_button.clicked.connect(self._on_send_clicked)

        # Load messages initially
        self._load_messages()

    def _on_text_changed(self, text: str):
        """
        Enable send button only if message is not empty.
        """
        self.send_push_button.setEnabled(bool(text.strip()))

    def _on_send_clicked(self):
        """
        Send message to chat via API.
        Reload message list after sending.
        """
        content = self.message_line_edit.text().strip()
        if not content:
            return
        try:
            self.api.send_message(self.chat_id, self.username, content)
        except Exception as e:
            print(f"[ChatScreen] send_message error: {e!r}")
            return

        # Clear input & disable button after sending
        self.message_line_edit.clear()
        self.send_push_button.setEnabled(False)

        # Reload chat messages
        self._load_messages()

    def _load_messages(self):
        """
        Load & display chat messages from API.
        Auto-scroll to bottom.
        """
        self.messages_list_widget.clear()
        try:
            msgs = self.api.list_messages(self.chat_id)
            for m in msgs:
                # Format timestamp: HH:MM
                ts = datetime.fromtimestamp(m.sent_at).strftime("%H:%M")
                item = QListWidgetItem(f"{m.sent_by} on {ts}: {m.content}")
                self.messages_list_widget.addItem(item)

                # Auto-scroll to bottom
                self.messages_list_widget.scrollToBottom()
        except Exception as e:
            print(f"[ChatScreen] list_messages error: {e!r}")