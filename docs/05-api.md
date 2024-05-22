# Doi2pmh

## API

The API is self-documented at `/api`.

### Authentification

#### With CURL
- call POST https://app.localtest.me/authentication_token with your email and password in the body:
```bash
curl --insecure \
  --request POST \
  --url https://app.localtest.me/authentication_token \
  --header 'Content-Type: application/json' \
  --data '{"email":"value","password":"value"}'
```
- get the token value in the answer
- add the Authorization header in your next requests with value 'Bearer token-value': 
```bash
curl --insecure \
  --request GET \
  --url http://app.localtest.me/api/dois/ \
  --header 'Authorization: Bearer eyJ0eXAiOiJKV1...rPXo8bqHo' \
  --header 'Content-Type: application/x-www-form-urlencoded' \
  --header 'accept: application/json'
```

Using [jq](https://jqlang.github.io/jq/), the token can be stored in a variable in one line:
```bash
TOKEN=$(curl --insecure \
  --request POST \
  --url https://app.localtest.me/authentication_token \
  --header 'Content-Type: application/json' \
  --data '{"email":"value","password":"value"}' \
  | jq -r .token)
```
or with sed:
```bash
TOKEN=$(curl --insecure \
  --request POST \
  --url https://app.localtest.me/authentication_token \
  --header 'Content-Type: application/json' \
  --data '{"email":"value","password":"value"}' \
  | sed 's/.*"token":"\(.*\)".*/\1/')
```

#### Through the user page (admin only)
* Go to "Users list"
* Click on "Get API token" next to name

[Back to summary](./00-summary.md)