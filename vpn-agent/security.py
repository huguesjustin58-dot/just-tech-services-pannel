import hashlib
import hmac
import os
import re
import time
from collections import OrderedDict
from fastapi import HTTPException, Request

USERNAME_RE = re.compile(r"^jts_[a-z0-9_]{3,24}$")
_NONCE_CACHE: OrderedDict[str, float] = OrderedDict()


def validate_username(username: str) -> str:
    if not USERNAME_RE.match(username):
        raise HTTPException(status_code=422, detail="Nom d'utilisateur VPN invalide.")
    return username


async def verify_hmac_request(request: Request) -> bytes:
    secret = os.environ.get("JTS_AGENT_HMAC_SECRET", "")
    if not secret:
        raise HTTPException(status_code=500, detail="Secret HMAC manquant.")

    ts = request.headers.get("X-JTS-Timestamp", "")
    nonce = request.headers.get("X-JTS-Nonce", "")
    sig = request.headers.get("X-JTS-Signature", "")

    if not ts.isdigit() or abs(time.time() - int(ts)) > 30:
        raise HTTPException(status_code=401, detail="Horodatage expiré.")

    if not nonce or nonce in _NONCE_CACHE:
        raise HTTPException(status_code=401, detail="Nonce invalide ou rejoué.")

    body = await request.body()
    canonical = f"{ts}.{nonce}.{request.method}.{request.url.path}.{body.decode('utf-8')}"
    expected = hmac.new(secret.encode(), canonical.encode(), hashlib.sha256).hexdigest()

    if not hmac.compare_digest(expected, sig):
        raise HTTPException(status_code=401, detail="Signature HMAC invalide.")

    _NONCE_CACHE[nonce] = time.time()
    while len(_NONCE_CACHE) > 2048:
        _NONCE_CACHE.popitem(last=False)

    return body
