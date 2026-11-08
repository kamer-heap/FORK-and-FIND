# Fork & Find - ER Diagram

```mermaid
erDiagram
    Users ||--o| Cafeterias : "owns (owner_id)"
    Cafeterias ||--o{ Menu_Items : "has"
    Menu_Items ||--o{ Reviews : "receives"
    Users ||--o{ Reviews : "writes"

    Users {
        int user_id PK
        varchar name
        varchar email UK
        varchar password "hashed with password_hash, VARCHAR 255"
        enum role "student, owner, admin"
        tinyint is_approved "owners wait for admin approval"
    }
    Cafeterias {
        int cafe_id PK
        varchar name
        varchar department
        varchar location
        varchar timings
        varchar days "opening days, for example Monday-Friday"
        varchar description "optional"
        varchar image "optional, file name in public/uploads"
        int owner_id FK "unique: one owner, one cafeteria"
    }
    Menu_Items {
        int item_id PK
        int cafe_id FK
        varchar name
        decimal price
        varchar category
        varchar description "optional"
        varchar image "optional, file name in public/uploads"
        tinyint available "0 = sold out"
    }
    Reviews {
        int review_id PK
        int item_id FK
        int user_id FK
        tinyint rating "1 to 5"
        varchar comment
        datetime date
    }
```

Rules:
- A cafeteria has at most one owner (`owner_id` is unique and may be empty).
- A cafeteria cannot have two menu items with the same name.
- One review per student per menu item: a second submit updates it (`UNIQUE(item_id, user_id)`).
- When a cafeteria is deleted, its menu items and their reviews are deleted too.
