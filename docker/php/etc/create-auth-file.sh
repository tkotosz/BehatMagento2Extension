#!/usr/bin/env sh

echo "Create auth.json"
/usr/bin/envsubst < /tmp/auth.json.tmpl > ~/.composer/auth.json
cat ~/.composer/auth.json
