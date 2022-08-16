---
sidebar_position: 2
---

# Class Diagram

Since we had to update and upgrade an existing MVP without existing
documentation, we had to have a different type of mindset.

Indeed, most applications are thought for and designed before actually being
built, but this time,we ad to work backwards from an existing prototype
to determine the domain model and use cases from the code.

This is why we chose to start documenting the entities and the class diagram,
then work our way to the use cases and sequence diagrams.

## Entities

This app is currently pretty simple, with only two Entities to account for:

- **Users**, which are used for Authentication and Authorization (see [the security docs](docs/category/security))
- **Tasks**, which are created and managed by a connected **User**

Both are defined in `projet8-TodoList/src/Entity`

## Diagram

The class diagram is the following:

![class](/img/uml/Class.png)