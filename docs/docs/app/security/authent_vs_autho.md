---
sidebar_position: 3
---

# Authentication Vs Authorization

Being **Authenticated**, aka **Connected** or **Logged in** to an application does not
necessary mean you have the right to do anything with it or go anywhere, this is where **Authorization**
comes in.

## Authentication

The authentication system for the **ToDoList** app is pretty simple: a form requiring your username
and your password, as defined in `security.yaml`: 

```yaml
form_login:
    login_path: login
    check_path: login_check
```

## Authorization

Any authenticated user cannot access every route, as we explained earlier. The conditions defined
are shown in `security.yaml`: 

```yaml
    access_control:
        - { path: ^/login, roles: PUBLIC_ACCESS }
        - { path: ^/users, roles: ROLE_ADMIN }
        - { path: ^/, roles: ROLE_USER }
```
This tells us that: 

- Anyone can access the login route `/login` (as shown by the necessary role: **PUBLIC_ACCESS**),
and that feels logic, if this route was protected, nobody could even log in !
- Anyone authenticated (**ROLE_USER**) can access almost every route, except for any starting with
`/users`
- Indeed, these routes require the **ROLE_ADMIN** to be accessed, only possessed by select users.
And since this condition is defined before the one for `^/`, it will always trigger first, and
block non-admin users from accessing these routes.

| *Note* |
|--------|

Every administrator is also able to access any route, since the **ROLE_ADMIN** includes the
**ROLE_USER** as defined here : 

```yaml
    role_hierarchy:
        ROLE_ADMIN: ROLE_USER
```
