---
sidebar_position: 3
---

# Use Cases

Concerning the different use cases of the application, some were described in
the project specification (when we were handled the MVP, a few instructions 
came along), some were still non-existent, but also specifically required
in said specifications, and a few more had to be determined through the
reading of the code and the writing of tests.

## List

### Public

- As a non-connected **User**, I can:
  - Log in if I have an account

### User

- As a connected **User**, I can:
  - See the list of all existing **Tasks**
  - Create a new **Task**
  - Update any existing **Task**
  - Toggle any existing **Task** (mark it as *Done* or *ToDo*)
  - Remove a **Task** that I have created

### Admin

- As a connected **Administrator**, I can:
  - Do anything a regular **User** can
  - Remove any existing **Task** IF it is not linked to any existing User
  - Manage **Users** (Create, Read, Update, Delete)

## Diagrams

These use cases can be represented by the following diagrams:

### Public

![UseCasePublic](/img/uml/UseCasePublic.png)

### User

![UseCaseUser](/img/uml/UseCaseUser.png)

### Admin

![UseCaseAdmin](/img/uml/UseCaseAdmin.png)