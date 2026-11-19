import json
import subprocess
from fastapi import FastAPI, Request
from pydantic import BaseModel, Field
from security import validate_username, verify_hmac_request

app = FastAPI(title="JUST TECH SERVICES VPN Agent")


class ProvisionPayload(BaseModel):
    username: str
    password: str
    expires_at: str
    max_sessions: int = Field(ge=1, le=50)
    quota_bytes: int = Field(ge=0)


@app.post("/v1/users/provision")
async def provision_user(request: Request):
    raw = await verify_hmac_request(request)
    data = ProvisionPayload(**json.loads(raw))
    uname = validate_username(data.username)

    subprocess.run(
        ["sudo", "/usr/local/sbin/jts-user-create.sh", uname, data.expires_at, str(data.max_sessions), str(data.quota_bytes)],
        input=f"{uname}:{data.password}\n".encode(),
        check=True,
    )
    return {"ok": True, "username": uname}


@app.post("/v1/users/suspend")
async def suspend_user(request: Request):
    raw = await verify_hmac_request(request)
    uname = validate_username(json.loads(raw)["username"])
    subprocess.run(["sudo", "usermod", "-L", uname], check=True)
    subprocess.run(["sudo", "pkill", "-TERM", "-u", uname], check=False)
    return {"ok": True, "username": uname}
