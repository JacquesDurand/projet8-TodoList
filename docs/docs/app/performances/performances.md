---
sidebar_position: 3
---

# Performances

During the ToDoList MVP's audit, it was asked of us to take a look at its performances.    
To be fair, it was not a simple task to audit performances for an application that does not run properly,
even in a development environment: No DBMS, no Migrations, compatibility issues between current/installable packages
and modern versions of PostgreSQL ... 

So keep in mind that as much as it was easier once the application was modernised, the numbers and graphs you will
see concerning the "*Legacy*" part of the application were already linked to a much cleaner app (Dockerized, PHP 7+,
Symfony 4.4 ...)

This said, looking at the following graphs, we can see that we still managed to gain a few milliseconds here and 
there on the main routes:

## "Legacy"

#### HomePage
![homepage](/img/prod-legacy/legacy_homepage.png)

#### Task List
![task_list](/img/prod-legacy/legacy_task_list.png)

#### Task Creation Page
![task_create](/img/prod-legacy/legacy_task_create.png)

#### User List
![user_list](/img/prod-legacy/legacy_user_list.png)

#### User Creation Page
![user_list](/img/prod-legacy/legacy_user_create.png)


## Prod on 'main'

#### HomePage
![homepage](/img/prod-main/prod_homepage.png)

#### Task List
![task_list](/img/prod-main/prod_task_list.png)

#### Task Creation Page
![task_create](/img/prod-main/prod_task_create.png)

#### User List
![user_list](/img/prod-main/prod_user_list.png)

#### User Creation Page
![user_list](/img/prod-main/prod_user_create.png)


## Conclusion

Gains: 
- Homepage : 79 ms
- Task List: 80 ms
- Task Create: 106 ms
- User List: 77 ms
- User Create: 87 ms