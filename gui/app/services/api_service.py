"""
ApiService:
Wraps all REST API calls to backend (http://localhost:8080).

Responsibilities:
- user creation
- chat creation
- listing joined & not joined chats
- joining chat
- listing messages
- sending message
"""

import requests
from typing import List
from ..models import Chat, User, Message

# Backend base URL
BACKEND_URL = "http://localhost:8080"

class ApiService:
    def __init__(self):
        """
        Initialize ApiService with a persistent requests.Session.
        """
        self.session = requests.Session()

    def create_user(self, username: str) -> None:
        """
        Create user with given username.
        POST /users
        """
        url = f"{BACKEND_URL}/users"
        payload = {"username": username}
        response = self.session.post(url, json=payload)
        response.raise_for_status()

    def create_chat(self, username: str, chat_name: str) -> None:
        """
        Create new chat with given name under user.
        POST /chats
        """
        url = f"{BACKEND_URL}/chats"
        payload = {
            "username": username,
            "name": chat_name
        }
        response = self.session.post(url, json=payload)
        response.raise_for_status()

    def list_chats_joined(self, username: str) -> List[Chat]:
        """
        List chats the user has already joined.
        GET /chats/joined?username=
        """
        url = f"{BACKEND_URL}/chats/joined"
        params = {"username": username}
        response = self.session.get(url, params=params)
        response.raise_for_status()
        chats_data = response.json()

        # Convert to list of Chat dataclass objects
        return [Chat(id=c["id"], name=c["name"], joined=True) for c in chats_data]

    def list_chats_not_joined(self, username: str) -> List[Chat]:
        """
        List chats the user has not yet joined.
        GET /chats/not-joined?username=
        """
        url = f"{BACKEND_URL}/chats/not-joined"
        params = {"username": username}
        response = self.session.get(url, params=params)
        response.raise_for_status()
        chats_data = response.json()

        # Convert to list of Chat dataclass objects
        return [Chat(id=c["id"], name=c["name"], joined=False) for c in chats_data]

    def join_chat(self, username: str, chat_id: int) -> None:
        """
        Join given chat.
        POST /chats/{chat_id}/join
        """
        url = f"{BACKEND_URL}/chats/{chat_id}/join"
        payload = {"username": username}
        response = self.session.post(url, json=payload)
        response.raise_for_status()

    def list_messages(self, chat_id: int) -> List[Message]:
        """
        List messages of given chat.
        GET /chats/{chat_id}/messages
        """
        url = f"{BACKEND_URL}/chats/{chat_id}/messages"
        response = self.session.get(url)
        response.raise_for_status()
        messages_data = response.json()

        # Convert to list of Message dataclass objects
        return [
            Message(sent_by=m["sent_by"], sent_at=int(m["sent_at"]), content=m["content"])
            for m in messages_data
        ]

    def send_message(self, chat_id: int, username: str, content: str) -> None:
        """
        Send message to given chat.
        POST /chats/{chat_id}/messages
        """
        url = f"{BACKEND_URL}/chats/{chat_id}/messages"
        payload = {
            "username": username,
            "content": content,
            "sent_at": int(__import__("time").time()),
        }
        response = self.session.post(url, json=payload)
        response.raise_for_status()