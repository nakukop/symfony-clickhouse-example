# INSTALL

1. Clone repo `git@gitlab.b2bdev.pro:backend/reports/report-service.git`;
2. Update submodules: `git submodule update --recursive --init`   
2. Inside docker execute:
   - `composer install`
   - `composer protobuf`
   - Run migrations up: `php bin/console clickhouse:migrate`
   - Run migrations down: `php bin/console clickhouse:migrate down [OPTIONAL --v=Version000001]`
   
**NOTE: `Version000002` is included in rollback procedure too.**

**NOTE: Use in docker CLICKHOUSE version ^21.9 (uuid functions stability)**