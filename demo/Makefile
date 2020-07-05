start:
	docker-compose start

build_cluster:
	#https://www.cnblogs.com/zhoujinyi/p/11606935.html
	docker exec -it cluster_redis_6390 redis-cli -a redisadmin --cluster create 127.0.0.1:6390 127.0.0.1:6391 127.0.0.1:6392 127.0.0.1:6393 127.0.0.1:6394 127.0.0.1:6395
	#docker exec -it cluster_redis_6390 redis-cli --cluster -a redisadmin create 127.0.0.1:6390 127.0.0.1:6391 127.0.0.1:6392 127.0.0.1:6393 127.0.0.1:6394 127.0.0.1:6395 --cluster-replicas 1

stop:
	docker-compose stop

