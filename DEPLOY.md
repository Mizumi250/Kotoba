# Deploy kotoba

Follow the steps **in order**. Copy and paste each command exactly. Lines starting with `#` are notes, not commands.
If something fails, jump to **Troubleshooting** at the bottom.

## What you are deploying

| Part | This export |
|------|-------------|
| Website / app | Next.js |
| Backend | Laravel (PHP) |
| Database | SQLite (file database/arkzen/kotoba.sqlite) |
| Realtime | yes (websockets) |
| Background jobs | no |

## Step 1 — Install Docker

- **Windows / Mac:** install **Docker Desktop** from docker.com, open it, and wait until it says it is running.
- **Linux server:** `curl -fsSL https://get.docker.com | sh`

Check it works:
```bash
docker --version
docker compose version
```
Both must print a version number.

## Step 2 — Run it on your own computer first

Open a terminal **inside this folder** (the one that contains `docker-compose.yml`).

1. The file `deploy.env` was created for you with random secrets. You can start right away.
2. Build and start everything (the first time can take 5–20 minutes, Rust and Spring Boot are the slowest):
```bash
docker compose --env-file deploy.env up -d --build
```
3. Check that everything is running (every line should say `running` or `healthy`):
```bash
docker compose --env-file deploy.env ps
```
4. Open **http://localhost:8080** in your browser.

This project uses **SQLite**. The database file is inside the backend container and kept in the Docker volume `backend-database`.

Useful commands (always in this folder):

| What you want | Command |
|---------------|---------|
| See live logs | `docker compose --env-file deploy.env logs -f` |
| See logs of one part | `docker compose --env-file deploy.env logs -f backend` |
| Stop (keeps your data) | `docker compose --env-file deploy.env down` |
| Start again | `docker compose --env-file deploy.env up -d` |
| Update after changing code | `docker compose --env-file deploy.env up -d --build` |
| Stop **and delete all data** | `docker compose --env-file deploy.env down -v` |

Backup (do this before updating a real site):
```bash
docker compose --env-file deploy.env cp backend:/app/database ./backup-database
docker compose --env-file deploy.env cp backend:/app/storage ./backup-storage
```

## Step 3 — Put it online (pick the kind of host that fits you)

The files in this folder do **not** depend on one company. Whatever you choose, three things must be true:

1. **One public address.** `/api/*` and `/storage/*` go to the backend, everything else goes to the website. The `proxy` container already does this.
2. **Secrets and settings** from `deploy.env` are set on the server.
3. **The data survives restarts** (the compose file already keeps it in Docker volumes).

### Host type A — a server you rent (VPS). Works with any provider
Any provider that gives you a Linux server with a public IP (DigitalOcean, Hetzner, Linode, Vultr, AWS Lightsail, Oracle Cloud, a company server…).

1. Create a server: **Ubuntu 24.04**, at least **2 GB RAM** (Rust and Spring Boot builds need more: 4 GB).
2. Connect: `ssh root@YOUR_SERVER_IP`
3. Install Docker on the server: `curl -fsSL https://get.docker.com | sh`
4. Copy this project folder to the server. From **your computer**, inside this folder:
```bash
rsync -av --exclude node_modules --exclude vendor --exclude .venv --exclude target ./ root@YOUR_SERVER_IP:~/kotoba/
```
   (No rsync? Use git instead: put the folder in a **private** repository, then `git clone` it on the server. `deploy.env` is not in git, so create it on the server from `deploy.env.example`.)
5. Point your domain to the server: in your domain's DNS settings add an **A record** → `YOUR_SERVER_IP`. Wait a few minutes.
6. On the server, edit the settings: `cd ~/kotoba && nano deploy.env` and set:
```ini
HTTP_PORT=80
HTTPS_PORT=443
SITE_ADDRESS=yourdomain.com
PUBLIC_URL=https://yourdomain.com
REALTIME_HOST=yourdomain.com
REALTIME_PORT=443
REALTIME_SCHEME=wss
```
7. Start it: `docker compose --env-file deploy.env up -d --build`
8. Open `https://yourdomain.com`. The HTTPS certificate is created automatically (needs ports 80 and 443 open in the provider's firewall).

### Host type B — a control panel that understands docker-compose
Panels such as Coolify, Dokploy or Portainer let you click instead of typing commands.

1. Put this folder in a Git repository (private is fine) and push it.
2. In the panel, create a new **Docker Compose** application from that repository. Use `docker-compose.yml`.
3. Copy every line of `deploy.env` into the panel's **Environment Variables** section (panels do not read `deploy.env` by themselves).
4. Set the domain in the panel. If the panel gives its own HTTPS, keep `SITE_ADDRESS=:80` and point the panel's domain at the `proxy` service, port 80.
5. Press **Deploy**.

### Host type C — your own computer or a home server (no public IP needed)
Good for demos. Run it with the steps above on your own machine, then expose port 8080 with a tunnel tool (Cloudflare Tunnel, Tailscale Funnel, ngrok…): tell the tool to forward to `http://localhost:8080`. The tool gives you a public address. Set `PUBLIC_URL` in `deploy.env` to that address and run `docker compose --env-file deploy.env up -d --build` again.

### Host type D — managed platforms that run one container per service
(Render, Railway, Fly.io, Google Cloud Run, Azure Container Apps, AWS ECS…). These do not read `docker-compose.yml`, so each service is created by hand. It works, but it is more steps — use A, B or C first if you can.

Create one service per row, all in the **same private network/project**:

| Service | Build from (folder with a Dockerfile) | Port | Public? |
|---------|----------------------------------------|------|---------|
| proxy | docker/ | 80 | YES — the only public one |
| backend | backend/ | 8001 | no (private) |
| frontend | frontend/ | 3001 | no (private) |
| reverb | same image as backend, different start command | 8081 | no (private) |

Then:
1. Give each service the environment variables listed in `docker-compose.yml` under its `environment:` section (values come from `deploy.env`).
2. On the `proxy` service set `BACKEND_UPSTREAM`, `FRONTEND_UPSTREAM` and `REALTIME_UPSTREAM` to the **private address and port** the platform shows for those services (format `host:port`). Example: `BACKEND_UPSTREAM=my-backend.internal:8001`.
3. Make only `proxy` public. Add your domain to it in the platform's settings.
4. SQLite lives in a file: attach a **persistent disk/volume** to the backend service and mount it at `/app/database` and `/app/storage`. Without a persistent disk your data is lost on every deploy.

## Troubleshooting

| Problem | What to do |
|---------|------------|
| `port is already allocated` | Something else uses port 8080. Change `HTTP_PORT=8081` in `deploy.env`, run the start command again, open http://localhost:8081 |
| `variable is not set ... Set DB_PASSWORD` | You forgot `--env-file deploy.env` in the command, or the value is empty in `deploy.env` |
| Page is blank or shows 502 | The backend may still be starting. Wait a minute, then run the logs command and read the last red lines |
| Build is very slow or "killed" | The server ran out of memory. Use a server with 4 GB RAM or add swap |
| Realtime/notifications do not update live | After changing a domain in `deploy.env` run the **update** command again, because the address is built into the website |
| HTTPS certificate not created | The domain's A record must point to the server **and** ports 80 and 443 must be open in the firewall |
| `permission denied` talking to Docker (Linux) | `sudo usermod -aG docker $USER` then log out and in again |
| I re-exported and the app looks old | Data in volumes is kept on purpose. If you do not need the old data: `docker compose --env-file deploy.env down -v` then start again |
