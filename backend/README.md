
# PHP Chat Backend

A simple RESTful chat backend built with:

- **Slim Framework** (API routing)
- **Eloquent ORM** (database layer)
- **Redis Pub/Sub** (real-time event notifications)
- **SQLite** (lightweight database)

Designed to work with the **PyQt Chat GUI client**.

---

## Features

✅ User registration / deletion  
✅ Create chat rooms  
✅ Join chat rooms  
✅ Send messages  
✅ List messages in a chat  
✅ List chats user has joined / not joined  
✅ Redis Pub/Sub for notifying frontend about new chats & messages  

---

## Architecture

```
+---------------------+
|  PyQt Chat GUI      |
+----------+----------+
           |
           | REST API (HTTP)
           |
+----------v----------+
| Slim PHP App        |
|  - UserController   |
|  - ChatController   |
|  - Services         |
|  - Repositories     |
+----------+----------+
           |
           | Eloquent ORM
           v
       SQLite DB
           |
           | Redis Pub/Sub
           v
      Redis (php-chat channel)
```

---

## Project Structure

```
composer.json
public/index.php         → Entry point (Slim app)
src/bootstrap.php        → Bootstraps Eloquent ORM
src/dependencies.php     → Dependency injection config
src/Routes/web.php       → API routes

src/Controller           → REST API controllers
src/Service              → Business logic
src/Repository           → Data access layer
src/Model                → Eloquent models
src/Schema/schema.sql    → DB schema

data/php-chat.db         → SQLite database
scripts/init.sh          → DB init script (optional)
```

---

## API Endpoints

### User

| Method | Endpoint                | Description           |
|--------|-------------------------|-----------------------|
| POST   | `/users`                | Create user           |
| DELETE | `/users/{username}`     | Delete user           |

### Chat

| Method | Endpoint                        | Description           |
|--------|---------------------------------|-----------------------|
| POST   | `/chats`                        | Create chat           |
| POST   | `/chats/{chatId}/join`          | Join chat             |
| POST   | `/chats/{chatId}/messages`      | Send message          |
| GET    | `/chats/{chatId}/messages`      | List messages in chat |
| GET    | `/chats/joined`                 | List joined chats     |
| GET    | `/chats/not-joined`             | List not joined chats |

---

## Redis Channel

- Channel name: `php-chat`
- Events:
  - `chat_created`
  - `message_sent`

---

## Notes

- Database is SQLite for simplicity. You can switch to MySQL/PostgreSQL if desired.
- Redis hostname in this example is configured as `broker` (for use with Docker). If running locally, update it to `localhost`.

---

## License

GPL 3.0 or Later
