# PHP Chat GUI Client (PyQt6)

A simple desktop GUI client for the PHP Chat backend API.

- Built with **Python 3.10+**
- Uses **PyQt6** for the UI
- Talks to REST API served by your `php-chat` backend
- Uses Redis pub/sub to live update chat messages

---

## Features

✅ User creation & login  
✅ Create new chats  
✅ Join existing chats  
✅ View available & joined chats  
✅ Open chat windows (one per chat)  
✅ Send messages  
✅ Live updates when messages are sent (via Redis)  

---

## Architecture

```
┌──────────┐       REST API        ┌─────────────┐
│ ChatApp  │  <---------------->   │ php-chat    │
│ (PyQt6)  │       HTTP            │ backend API │
└──────────┘                       └─────────────┘
           ▲                                 ▲
           │   Redis pub/sub (php-chat)      │
           └─────────────────────────────────┘
```

---

## Project Structure

```
app/
    __init__.py
    app.py                  # Main ChatApp window
    models/                 # Data models (Chat, User, Message)
    screens/                # ChatScreen windows
    services/               # API and Redis services
    ui/                     # Qt Designer UI files (.ui)
main.py                     # App entry point
```

---

## Prerequisites

- Python >= 3.10
- Redis running locally at `localhost:6379`
- PHP backend API running at `http://localhost:8080`

---

## Installation

1. Clone this repo:

    ```bash
    git clone https://github.com/your-repo/php-chat-gui.git
    cd php-chat-gui
    ```

2. Create and activate a virtualenv:

    ```bash
    python3 -m venv venv
    source venv/bin/activate
    ```

3. Install requirements:

    ```bash
    pip install -r requirements.txt
    ```

    **Example requirements.txt**:

    ```txt
    PyQt6
    requests
    redis>=4.5.0
    ```

---

## Running

```bash
python main.py your_username
```

- You must run the PHP Chat backend first (`php-chat`) and have Redis running.
---

## How It Works

- The GUI talks to `http://localhost:8080` via REST API
- It also subscribes to Redis channel `php-chat` to receive:
    - `chat_created` events
    - `message_sent` events
- This allows the UI to automatically refresh chat lists and chat windows

---

## Notes

- One chat window is opened at most per chat.
- Messages auto-refresh live when someone sends a message in that chat.
- You can open multiple chat windows.

---

## License

GPL 3.0 or Later
