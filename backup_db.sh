#!/bin/bash

# 导入 .env 环境变量
source ./.env

# 要备份的表
tables="menus product_categories"

# 执行 sql 备份
mysqldump --host="${DB_HOST}" --port=${DB_PORT} --user="${DB_USERNAME}" --password="${DB_PASSWORD}" -t ${DB_DATABASE} ${tables} > ./database/back_up.sql
