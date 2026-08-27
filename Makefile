compose_up_local_d:
	docker-compose  -f docker-compose.local.yml up  --build -d

compose_up_local:
	docker-compose  -f docker-compose.local.yml up  --build

compose_down_local:
	docker-compose  -f docker-compose.local.yml down

compose_clean_local:
	docker-compose  -f docker-compose.local.yml down
	docker-compose  -f docker-compose.local.yml rm
