# GNS OLT event trigger from MikroTik

## Safety and scope

- This feature uses an HTTP event from MikroTik PPP profile hooks; it does **not** use PPP Listen.
- It does not add/remove PPP sessions, disconnect users, change RADIUS, or change firewall/NAT.
- Do not replace an existing profile `on-down` or `on-up` script. Merge the small `/tool fetch` call into the existing script only after backing it up.
- The GNS endpoint reads ONU status using the existing `OltService::findByUsername()`. A PPP event alone is not treated as proof of an optical outage.
- A request is rejected unless the shared secret matches `OLT_EVENT_TOKEN`.

## Deploy on GNS_DEV first

1. Deploy branch `feat/olt-mikrotik-event-trigger` to the development copy only.
2. Generate a random secret on the GNS server, for example with `openssl rand -hex 32`.
3. Add `OLT_EVENT_TOKEN=<generated-secret>` to the GNS_DEV `.env`. Never commit this secret.
4. Run `php artisan config:clear` (and `php artisan route:clear` if routes are cached).
5. Verify the route exists with `php artisan route:list --path=api/olt/event`.
6. Confirm `TELEGRAM_ENABLED=true`, `TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHAT_ID`, and the KUWU OLT credentials are configured in GNS_DEV.

The endpoint is `POST /api/olt/event` and requires header `X-OLT-Event-Token: <same-secret>`.

Example JSON body:
```json
{
  "router": "KUWU",
  "username": "customer-pppoe-username",
  "event": "down"
}
```

Allowed events are `down` and `up`. The controller checks the named active customer, reads the ONU status from the OLT, and sends Telegram only when a verified status transition warrants a notification. If OLT lookup fails or status is unknown, it returns an error and does not mark the customer offline.

## MikroTik integration (after GNS_DEV endpoint test)

Use the RouterOS PPP profile `on-down` / `on-up` hook only after reviewing and backing up the existing profile scripts. Do not enable `/ppp aaa set use-radius` or PPP Listen as part of this feature.

The event call shape is:

```routeros
/tool fetch url="https://YOUR-GNS-HOST/api/olt/event" http-method=post \
  http-header-field="Content-Type:application/json,X-OLT-Event-Token:YOUR_SHARED_SECRET" \
  http-data="{\"router\":\"KUWU\",\"username\":\"$user\",\"event\":\"down\"}" \
  keep-result=no
```

For the `on-up` hook, use the same call with `"event":"up"`.

Replace the host and secret with the actual GNS_DEV endpoint and the secret stored in that environment. Do not paste the secret into GitHub, screenshots, or logs. First test a manual POST from a controlled client against GNS_DEV. Only then integrate the call into the existing hooks, preserving every existing command. Do not test by disconnecting a real customer; validate the endpoint with a known test customer/request first.

## Operational limitations

- This is event-triggered, not continuous OLT polling.
- It only evaluates the ONU for the PPP username supplied by the event.
- If an OLT/ONU outage does not cause a PPP session event on MikroTik, this trigger will not fire for that outage.
- Telegram delivery and OLT reachability must be tested in GNS_DEV before production rollout.
