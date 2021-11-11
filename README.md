# MIGRATION for CK
 
Inside docker execute:
 - Run migrations up: `php bin/console clickhouse:migrate`
 - Run migrations down: `php bin/console clickhouse:migrate down [OPTIONAL --v=Version000001]`
   
**NOTE: `Version000002` is included in rollback procedure too.**

**NOTE: Use in docker CLICKHOUSE version ^21.9 (uuid functions stability)**
