"""
RedisService:
- Subscribes to Redis pub/sub channel 'php-chat'
- Listens for events ('chat_created', 'message_sent')
- Dispatches to registered handlers
- Runs Redis listener loop on background thread
"""

import asyncio
import json
from typing import Callable, Dict

import redis.asyncio as aioredis

# Redis connection settings
REDIS_HOST = "localhost"
REDIS_PORT = 6379
REDIS_CHANNEL = "php-chat"

class RedisService:
    def __init__(self):
        """
        Initialize RedisService.
        """
        self.redis = aioredis.Redis(host=REDIS_HOST, port=REDIS_PORT)
        self.pubsub = self.redis.pubsub()

        # Message type → handler callback
        self.handlers: Dict[str, Callable[[dict], None]] = {}

        # Control flag for main loop
        self.running = False

    def register_handler(self, msg_type: str, handler_fn: Callable[[dict], None]):
        """
        Register handler for given message type.
        """
        self.handlers[msg_type] = handler_fn
        print(f"[RedisService] Handler registered for '{msg_type}'")

    async def connect(self):
        """
        Connect to Redis and subscribe to channel.
        """
        await self.pubsub.subscribe(REDIS_CHANNEL)
        print(f"[RedisService] Subscribed to '{REDIS_CHANNEL}'")
        self.running = True

    async def run_forever(self):
        """
        Main Redis listener loop.
        Will keep reading messages while self.running == True.
        """
        print("[RedisService] Listening loop running...")
        while self.running:
            message = await self.pubsub.get_message(ignore_subscribe_messages=True, timeout=1.0)
            if message is None:
                # No message → avoid busy loop
                await asyncio.sleep(0.1)
                continue

            try:
                # Decode JSON message payload
                data = json.loads(message["data"].decode("utf-8"))
                msg_type = data.get("type")

                # Dispatch to matching handler if available
                if msg_type and msg_type in self.handlers:
                    print(f"[RedisService] Dispatching message of type '{msg_type}'")
                    self.handlers[msg_type](data)
                else:
                    print(f"[RedisService] Unhandled message type: {msg_type}, full message: {data}")
            except json.JSONDecodeError:
                print(f"[RedisService] Received non-JSON message: {message['data']}")

        # Exiting loop → cleanup
        print("[RedisService] Cleaning up Redis subscription")
        await self.pubsub.unsubscribe(REDIS_CHANNEL)
        await self.pubsub.close()
        await self.redis.close()
        print("[RedisService] Disconnected and cleaned up")