"""
Main ChatApp window.

Features:
- User creation & login
- Display joined & available chats
- Create chat
- Join chat
- Open chat windows (one per chat, no duplicates)
- Redis event handling: chat_created, message_sent
"""

import sys
import threading
import asyncio
from pathlib import Path
from typing import Dict

from PyQt6.QtCore import QTimer
from PyQt6.QtGui import QCloseEvent
from PyQt6.uic.load_ui import loadUi
from PyQt6.QtWidgets import (
    QMainWindow, QListWidget, QLineEdit,
    QPushButton, QLabel, QListWidgetItem,
    QMdiArea
)

from .services import ApiService, RedisService
from .screens import ChatScreen

class ChatApp(QMainWindow):
    def __init__(self, username: str):
        """
        Initialize ChatApp and start Redis listener.
        """
        super().__init__()
        loadUi(
            str(Path(__file__).resolve().parent / "ui" / "chat_app.ui"),
            self
        )
        self.open_windows: Dict[int, ChatScreen] = {}  # {chat_id: ChatScreen}

        self.username = username
        self.api = ApiService()
        self.redis = RedisService()

        # Register Redis event handlers
        self.redis.register_handler("chat_created", self._on_chat_created)
        self.redis.register_handler("message_sent", self._on_message_sent)

        # Get UI elements
        self.username_label            = self.findChild(QLabel, "username_label")
        self.chat_name_line_edit       = self.findChild(QLineEdit, "chat_name_line_edit")
        self.create_chat_push_button   = self.findChild(QPushButton, "create_chat_push_button")
        self.join_chat_push_button     = self.findChild(QPushButton, "join_chat_push_button")
        self.available_list_widget     = self.findChild(QListWidget, "available_list_widget")
        self.joined_list_widget        = self.findChild(QListWidget, "joined_list_widget")
        self.mdiArea                   = self.findChild(QMdiArea, "mdiArea")

        # Set initial UI state
        self.username_label.setText(f"Hi, {self.username}!")
        self._wire_ui()

        # Start Redis subscriber in background thread
        self.redis_thread = threading.Thread(
            target=self._run_redis_loop,
            daemon=True
        )
        self.redis_thread.start()

        # Initialize user & chats after UI loads
        QTimer.singleShot(0, self._initialize_user_and_chats)

    def _wire_ui(self):
        """
        Connect UI widgets to event handlers.
        """
        # Enable Create Chat button when text is not empty
        self.chat_name_line_edit.textChanged.connect(
            lambda txt: self.create_chat_push_button.setEnabled(bool(txt.strip()))
        )
        self.create_chat_push_button.clicked.connect(self._on_create_chat_clicked)

        # Enable Join Chat button when one available chat selected
        self.available_list_widget.itemClicked.connect(
            lambda _: self.join_chat_push_button.setEnabled(
                0 < len(self.available_list_widget.selectedItems()) < 2
            )
        )
        self.join_chat_push_button.clicked.connect(self._on_join_chat_clicked)

        # Open chat window when joined chat is clicked
        self.joined_list_widget.itemClicked.connect(self._on_open_chat_clicked)

    def _run_redis_loop(self):
        """
        Run Redis event loop (in background thread).
        """
        asyncio.run(self._redis_loop())

    async def _redis_loop(self):
        """
        Redis async loop: subscribe & handle events.
        """
        await self.redis.connect()
        print("[ChatApp] Redis subscriber started")
        await self.redis.run_forever()
    
    def closeEvent(self, a0: QCloseEvent | None) -> None:
        """
        Clean shutdown of Redis thread on window close.
        """
        print("[ChatApp] Closing app, stopping Redis...")
        self.redis.running = False
        self.redis_thread.join(timeout=2)
        print("[ChatApp] Redis thread stopped")
        return super().closeEvent(a0)

    def _initialize_user_and_chats(self):
        """
        Create/login user and load chat lists.
        """
        print(f"[ChatApp] Creating/logging in user: {self.username}")
        try:
            self.api.create_user(self.username)
        except Exception as err:
            print(f"[ChatApp] create_user error: {err!r}")

        self._refresh_chats()

    def _refresh_chats(self):
        """
        Load joined and available chat lists from API.
        """
        # Load joined chats
        try:
            joined = self.api.list_chats_joined(self.username)
            self.joined_list_widget.clear()
            for c in joined:
                self.joined_list_widget.addItem(
                    QListWidgetItem(f"{c.name} (#{c.id})")
                )
            print(f"[ChatApp] Loaded {len(joined)} joined chats")
        except Exception as err:
            print(f"[ChatApp] list_chats_joined error: {err!r}")

        # Load not-joined chats
        try:
            avail = self.api.list_chats_not_joined(self.username)
            self.available_list_widget.clear()
            for c in avail:
                self.available_list_widget.addItem(
                    QListWidgetItem(f"{c.name} (#{c.id})")
                )
            print(f"[ChatApp] Loaded {len(avail)} available chats")
        except Exception as err:
            print(f"[ChatApp] list_chats_not_joined error: {err!r}")

    # UI callbacks

    def _on_create_chat_clicked(self):
        """
        Create new chat (via API).
        """
        chat_name = self.chat_name_line_edit.text().strip()
        if not chat_name:
            return

        print(f"[ChatApp] → createChat: {chat_name}")
        try:
            self.api.create_chat(self.username, chat_name)
            print(f"[ChatApp] API call succeeded, waiting for Redis event to refresh.")
        except Exception as err:
            print(f"[ChatApp] create_chat error: {err!r}")
        finally:
            self.chat_name_line_edit.clear()

    def _on_join_chat_clicked(self):
        """
        Join selected chat (via API).
        """
        items = self.available_list_widget.selectedItems()
        if not items:
            return

        text = items[0].text()
        try:
            chat_id = int(text[text.rfind('#') + 1 : -1])
        except ValueError:
            print(f"[ChatApp] could not parse chat id from '{text}'")
            return

        print(f"[ChatApp] → joinChat: id={chat_id}")
        try:
            self.api.join_chat(self.username, chat_id)
            print(f"[ChatApp] API join_chat succeeded, waiting for Redis event to refresh.")
        except Exception as err:
            print(f"[ChatApp] join_chat error: {err!r}")
        finally:
            self.available_list_widget.clearSelection()
            self.join_chat_push_button.setEnabled(False)

    def _on_open_chat_clicked(self):
        """
        Open chat window (or bring existing window to front).
        """
        items = self.joined_list_widget.selectedItems()
        if not items:
            return

        full_text = items[0].text()
        name_part = full_text[: full_text.rfind(" (#")]
        try:
            chat_id = int(full_text[full_text.rfind('#') + 1 : -1])
        except ValueError:
            print(f"[ChatApp] could not parse chat id from '{full_text}'")
            return

        if chat_id in self.open_windows:
            return  # already open

        # Open new chat window
        screen = ChatScreen(self.username, self.api, chat_id)
        screen.setWindowTitle(name_part)
        self.mdiArea.addSubWindow(screen)
        screen.show()

        # Track window to avoid duplicates
        self.open_windows[chat_id] = screen
        screen.destroyed.connect(lambda _, cid=chat_id: self.open_windows.pop(cid, None))

    # Redis event handlers

    def _on_chat_created(self, data: dict):
        """
        Handle chat_created Redis event: refresh chats.
        """
        print("[ChatApp] event → chat_created")
        QTimer.singleShot(0, self._refresh_chats)

    def _on_message_sent(self, data: dict):
        """
        Handle message_sent Redis event: refresh open chat window.
        """
        print("[ChatApp] event → message_sent")

        try:
            chat_id = int(data.get("chatId", -1))
        except (TypeError, ValueError):
            print(f"[ChatApp] invalid chatId in message_sent payload: {data.get('chatId')!r}")
            return

        if chat_id in self.open_windows:
            window = self.open_windows[chat_id]
            QTimer.singleShot(0, window._load_messages)