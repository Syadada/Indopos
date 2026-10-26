import sqlite3
import os

db_path = 'db.sqlite3'
conn = sqlite3.connect(db_path)
conn.execute("PRAGMA foreign_keys = OFF;")

cur = conn.cursor()
cur.execute("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")
tables = cur.fetchall()

for name, sql in tables:
    if name.startswith('auth_') or name.startswith('django_'):
        continue  # Django core tables already have proper primary keys
    
    # Check if id column exists
    cur.execute(f"PRAGMA table_info({name})")
    cols = cur.fetchall()
    col_names = [c[1] for c in cols]
    
    if 'id' in col_names:
        # Check if id is already a primary key
        id_col = next(c for c in cols if c[1] == 'id')
        is_pk = id_col[5] > 0
        
        if not is_pk:
            print(f"Fixing table '{name}' to have 'id' INTEGER PRIMARY KEY AUTOINCREMENT...")
            
            # 1. Update any NULL ids to rowid
            conn.execute(f"UPDATE \"{name}\" SET id = rowid WHERE id IS NULL;")
            
            # 2. Build new table definition
            # Replace '"id" INTEGER' with '"id" INTEGER PRIMARY KEY AUTOINCREMENT'
            new_sql = sql
            if '"id" INTEGER' in new_sql:
                new_sql = new_sql.replace('"id" INTEGER', '"id" INTEGER PRIMARY KEY AUTOINCREMENT', 1)
            elif '`id` INTEGER' in new_sql:
                new_sql = new_sql.replace('`id` INTEGER', '`id` INTEGER PRIMARY KEY AUTOINCREMENT', 1)
            elif 'id INTEGER' in new_sql:
                new_sql = new_sql.replace('id INTEGER', 'id INTEGER PRIMARY KEY AUTOINCREMENT', 1)
            else:
                print(f"  Warning: Could not pattern match id column in {name}: {sql}")
                continue
            
            # Replace table name with temp table name
            temp_name = f"{name}__pkhack"
            temp_sql = new_sql.replace(f'"{name}"', f'"{temp_name}"', 1).replace(f'`{name}`', f'`{temp_name}`', 1).replace(f' {name} ', f' {temp_name} ', 1)
            
            # Execute migration
            conn.execute(f"DROP TABLE IF EXISTS \"{temp_name}\";")
            conn.execute(temp_sql)
            
            cols_str = ', '.join(f'"{c}"' for c in col_names)
            conn.execute(f"INSERT INTO \"{temp_name}\" ({cols_str}) SELECT {cols_str} FROM \"{name}\";")
            conn.execute(f"DROP TABLE \"{name}\";")
            conn.execute(f"ALTER TABLE \"{temp_name}\" RENAME TO \"{name}\";")
            print(f"  [OK] Table '{name}' successfully migrated with Primary Key AutoIncrement!")

conn.commit()
conn.execute("PRAGMA foreign_keys = ON;")
conn.close()
print("\n[SUKSES] Seluruh tabel database SQLite telah diperbaiki dengan PRIMARY KEY AUTOINCREMENT.")
