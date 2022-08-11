---
sidebar_position: 4
---

# Users

The users in our system are persisted via [Doctrine](https://www.doctrine-project.org/)
into our PostgreSql database.  
As requested, only the administrators have the rights to manage (Create, Edit, Delete) Users.
Nevertheless, to be able to understand the application correctly, every developer working on this 
app has the possibility to load **Fixtures**, aka fake data, into the database, and notably users
of different kind, as shown in `fixtures/user.yaml`:

```yaml
App\Entity\User:
    user_{1..9}:
        userName: <name()>
        password: <hashPassword(word())>
        email: <email()>
        roles: ['ROLE_USER']
    user_test:
        userName: 'admin'
        password: <hashPassword('Azerty123*')>
        email: 'admin@admin.com'
        roles: ['ROLE_ADMIN']
    user_test_2:
        userName: 'admin2'
        password: <hashPassword('Azerty123*')>
        email: 'admin2@admin.com'
        roles: ['ROLE_ADMIN']
    user_test_non_admin:
        username: 'random'
        password: <hashPassword('Azerty123*')>
        email: 'random@random.com'
        roles: [ 'ROLE_USER' ]
    user_anon:
        username: 'Anonymous'
        password: <hashPassword('@nonymou$')>
        email: 'anon@anon.com'
        roles: [ 'ROLE_USER' ]
```

2 Admins, 1 Anonymous User, 1 random (but authenticable non-admin User), and a few fillers.