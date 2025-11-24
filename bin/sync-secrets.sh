#!/bin/bash

# Sync Secrets from Bitwarden to GitHub
# Requires: bw (Bitwarden CLI) and gh (GitHub CLI) installed and authenticated.

# Check if tools are installed
if ! command -v bw &> /dev/null; then
    echo "Error: Bitwarden CLI (bw) is not installed."
    exit 1
fi

if ! command -v gh &> /dev/null; then
    echo "Error: GitHub CLI (gh) is not installed."
    exit 1
fi

# Check if logged in to Bitwarden
if ! bw status | grep -q "unlocked"; then
    echo "Error: Bitwarden vault is locked or not logged in. Run 'bw login' or 'bw unlock' first."
    exit 1
fi

# Configuration - Replace with your Bitwarden Item ID
# You can find the ID by running: bw list items --search "My Server Secrets"
BITWARDEN_ITEM_ID="<YOUR_BITWARDEN_ITEM_ID>"

echo "Fetching secrets from Bitwarden..."
SECRETS_JSON=$(bw get item $BITWARDEN_ITEM_ID)

# Extract secrets (assuming they are stored as custom fields or notes)
# Adjust the jq query based on how you store your secrets in Bitwarden
SSH_HOST=$(echo $SECRETS_JSON | jq -r '.fields[] | select(.name=="SSH_HOST") | .value')
SSH_USER=$(echo $SECRETS_JSON | jq -r '.fields[] | select(.name=="SSH_USER") | .value')
SSH_KEY=$(echo $SECRETS_JSON | jq -r '.fields[] | select(.name=="SSH_KEY") | .value')

if [ -z "$SSH_HOST" ] || [ -z "$SSH_USER" ] || [ -z "$SSH_KEY" ]; then
    echo "Error: Could not retrieve all secrets. Ensure fields SSH_HOST, SSH_USER, and SSH_KEY exist in the Bitwarden item."
    exit 1
fi

echo "Setting secrets in GitHub..."
gh secret set SSH_HOST --body "$SSH_HOST"
gh secret set SSH_USER --body "$SSH_USER"
gh secret set SSH_KEY --body "$SSH_KEY"

echo "Secrets synced successfully!"
