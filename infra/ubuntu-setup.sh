#!/usr/bin/env bash
set -euo pipefail

echo "[1/5] Création des répertoires /etc/jts et /var/lib/jts..."
mkdir -p /etc/jts/quotas /etc/jts/maxuser /var/lib/jts/usage /run/jts-agent

echo "[2/5] Installation du reaper Quotas & MaxUser..."
install -m 0755 infra/scripts/jts-ssh-quota-reaper.sh /usr/local/sbin/jts-ssh-quota-reaper.sh
install -m 0644 infra/systemd/jts-quota-reaper.service /etc/systemd/system/jts-quota-reaper.service
install -m 0644 infra/systemd/jts-quota-reaper.timer /etc/systemd/system/jts-quota-reaper.timer

echo "[3/5] Configuration sudoers restreinte pour jts-provisioner..."
if ! id -u jts-provisioner >/dev/null 2>&1; then
    useradd -m -s /bin/bash jts-provisioner
fi
install -m 0440 infra/sudoers/jts-provisioner /etc/sudoers.d/jts-provisioner

echo "[4/5] Désactivation DNSStubListener (libération UDP 53 pour SlowDNS)..."
mkdir -p /etc/systemd/resolved.conf.d
printf "[Resolve]\nDNSStubListener=no\n" > /etc/systemd/resolved.conf.d/jts-slowdns.conf
systemctl restart systemd-resolved || true

echo "[5/5] Activation du timer jts-quota-reaper..."
systemctl daemon-reload
systemctl enable --now jts-quota-reaper.timer
echo "Setup Ubuntu terminé."
