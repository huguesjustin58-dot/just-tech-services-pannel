# JUST TECH SERVICES PANEL

Plateforme SaaS commerciale de gestion d'abonnements VPN, facturation Mobile Money (FCFA) et orchestration d'infrastructure privée (**SSH**, **SSH SlowDNS**, **SSH-WSS**) pour **JUST TECH SERVICES**.

---

## 1. Caractéristiques Clés

- **Accès à Trois Niveaux (Laravel 11 + Filament v3)** :
  1. `Visiteur (/)` : vitrine publique des forfaits & paiements Mobile Money, sans redirection forcée vers `/login`.
  2. `Espace Client (/client/dashboard)` : inscription publique verrouillée au rôle `client`, isolation par Policies (`LicensePolicy`, `PaymentPolicy`, `CloudActivationCodePolicy`), génération Code Cloud Android ou `.npvt` iPhone.
  3. `Administration Privée (/admin)` : protégée par `EnsureUserIsAdmin` (retour `404` pour visiteurs et clients, lien masqué dans le DOM), contrôle total des Revendeurs, Sous-revendeurs, attribution de Gigas (Go), paiements et configurations.
- **Revendeurs & Sous-revendeurs (Stock en Gigas)** :
  - L'Administrateur crée librement les comptes Revendeur / Sous-revendeur et leur attribue un volume exact de Gigas (`gb_balance`).
  - Les Revendeurs & Sous-revendeurs achètent leurs forfaits directement avec leurs Gigas disponibles (déduction atomique via `CreditLedgerService`).
- **Double Pont Infrastructure VPN** :
  - **Pont Clé SSH Sécurisée** (`SshVpnBridgeInterface` + `SshKeyVpnBridgeService` via `phpseclib3`) : gestion multi-VPS distante par clé Ed25519 + vérification empreinte SHA-256 hôte, quotas bande passante `iptables` par UID, limites simultanées `maxuser`.
  - **Agent Local Isolé** (`vpn-agent` FastAPI) : communication Unix Socket signée HMAC-SHA256 + anti-replay (±30s + nonce).
- **Facturation Mobile Money & FCFA (Gigas uniquement)** :
  - Formules NPV Tunnel : `5 Go` (500 FCFA), `10 Go` (1 000 FCFA), `20 Go` (2 000 FCFA), `25 Go` (2 500 FCFA), `40 Go` (4 000 FCFA), `60 Go` (5 000 FCFA) + `Offre Test Gratuit` (3 Go / 2h).
  - Paiement : canaux Mobile Money configurables par variables d'environnement privées, avec bascule au plafond Orange.

---

## 2. Prérequis Système (VPS Ubuntu 22.04 / 24.04 LTS)

- 2 vCPU minimum, 4 Go RAM, 40 Go NVMe.
- Docker Engine 24+ et Docker Compose v2.
- Ports ouverts : `22/tcp` (SSH), `80/tcp` & `443/tcp` (HTTPS/WSS), `53/udp` (SlowDNS).
- Domaine géré via Cloudflare (`panel.justtechservices.xyz`, `api.justtechservices.xyz`, `status.justtechservices.xyz`).

---

## 3. Démarrage Rapide (5 Commandes)

```bash
# 1. Décompresser l'archive finale
unzip JUST_TECH_SERVICES_PANEL_FINAL.zip -d /opt/justtech-panel && cd /opt/justtech-panel

# 2. Préparer l'environnement et choisir les identifiants initiaux d'administration
cp backend/.env.example backend/.env
# Dans backend/.env, définir JTS_SUPERADMIN_EMAIL et un JTS_SUPERADMIN_PASSWORD unique (16 caractères minimum).
# Ne jamais publier backend/.env. Le script de déploiement génère les autres secrets manquants.

# 3. Démarrer la stack Docker non-root (PostgreSQL 16, Redis 7, PHP-FPM, Horizon, Nginx)
docker compose --env-file backend/.env up -d --build

# 4. Exécuter les migrations PostgreSQL et le seeder initial
docker compose exec app php artisan migrate --seed --force

# 5. Vérifier la santé globale & synchroniser les comptes Linux VPN
docker compose exec app php artisan jts:vpn-sync-accounts
```

---

## 4. Structure du Projet

```text
JUST_TECH_SERVICES_PANEL/
├── docker-compose.yml              # Stack Docker stricte non-root (UID 1000)
├── Makefile                        # Raccourcis build, test, migrate, backup
├── docker/
│   ├── php/Dockerfile              # PHP 8.3-FPM + pgsql + redis + gmp + sockets
│   └── nginx/default.conf          # Reverse proxy Nginx interne
├── backend/                        # Application Laravel 11 + Filament v3
│   ├── app/Contracts/Vpn/          # Contrats SshVpnBridgeInterface & ProtocolDriver
│   ├── app/Services/Vpn/           # SshKeyVpnBridgeService, VpnTransportResolver, LoadBalancer
│   ├── app/Services/Billing/       # CreditLedgerService (ACID lockForUpdate)
│   ├── app/Services/Config/        # Générateurs NPV Tunnel & NapsternetV
│   ├── app/Enums/                  # Enums PHP 8.3 (UserRole, LicenseStatus, PaymentMethod...)
│   ├── database/migrations/        # Schéma PostgreSQL indexé pour 1 000+ clients
│   └── tests/Feature/              # Tests isolation revendeur, idempotence, SSH bridge
├── vpn-agent/                      # Microservice Python FastAPI isolé (Unix socket + HMAC)
├── infra/                          # Scripts Ubuntu, SlowDNS dnstt, Fail2ban, Nginx hôte
└── docs/                           # Architecture détaillée & Runbook d'exploitation
```
