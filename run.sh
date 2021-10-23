#!/bin/bash

echo "Starting containers" && docker-compose up -d
echo "Running Unit tests" && docker exec cmr-staging_staging_1 composer test:coverage
echo "Running Integration tests" && docker exec cmr-staging_integration_test_1 composer test
docker stop cmr-staging_integration_test_1 > /dev/null
echo "Swagger running in http://$(docker port swagger_ui_container 8080)"
echo "Staging API serve in http://$(docker port cmr-staging_staging_1 80)"
