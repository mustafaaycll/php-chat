from dataclasses import dataclass

@dataclass
class Message:
    sent_by: str
    sent_at: int
    content: str