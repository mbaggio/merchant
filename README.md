# Merchants RESTFull solution
    Built with Lumen 7.2.1 & Swoole
    Using Swagger 3.0, RedisCache, MySQL8 and ElasticSearch
    
    - Introduction: https://www.youtube.com/watch?v=SwHkpzeDmgc
    - Introduction document: https://bit.ly/2Y9CqWm
    - DB_Schema can be found in ./mysql/DB_Schema.png

## What it does?
    It manages all the aspects arround Merchants, it's not only a CRUD for this entity as it manages 
    relationships with other entities. It also deploys different containers in order to use a MySQL 
    database engine and a Redis Cache.
    In addition to a connection to a ElasticSearch service in the cloud.

## How can I Install it?
### With Docker
    - Get Docker
        https://docs.docker.com/engine/install/      
   
    - And then follow the following instructions:
        - Cone this project: git clone https://github.com/mbaggio/merchant.git
        - Execute ./deploy.sh
        
    - Please look at the following video for more information: https://www.youtube.com/watch?v=80WciCmPSlE 

### Where I can see real time reports?
    Reports were integrated with ElasticSearch, and it depends on the executed endpoint  the infromation 
    that will be exported to ElasticSearch.
    
    ElasticSearch credentials are in the introduction document, and will work for a few days because it 
    is not a paid account. In order to see how it works, please look at this final video, which also 
    shows a performance test: https://www.youtube.com/watch?v=3Cm3UPLvr3E
            
            
            
                