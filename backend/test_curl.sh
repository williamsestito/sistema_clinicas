#!/bin/bash
rm -f /tmp/ck.txt
HOST="http://nginx"

# Get login page and CSRF token
TOKEN=$(curl -sS -c /tmp/ck.txt ${HOST}/login | grep -oP 'name="_token"[^>]*value="\K[^"]+' | head -1)
echo "TOKEN: ${TOKEN}"

# Login (follow redirects, save cookies)
HTTP_CODE=$(curl -sS -o /dev/null -w '%{http_code}' -b /tmp/ck.txt -c /tmp/ck.txt -X POST \
  -d "_token=${TOKEN}&email=admin@admin.com&password=123123" \
  ${HOST}/login)
echo "Login Response: ${HTTP_CODE}"

echo "--- Dashboard ---"
curl -sS -o /dev/null -w '%{http_code}\n' -b /tmp/ck.txt -L ${HOST}/admin/dashboard

echo "--- Search JSON (1 char - should return empty) ---"
curl -sS -b /tmp/ck.txt "${HOST}/admin/patients/search-json?q=a"
echo ""

echo "--- Search JSON (2 chars) ---"
curl -sS -b /tmp/ck.txt "${HOST}/admin/patients/search-json?q=ma"
echo ""

echo "Done."
