# CIBOARD with Docker(PHP, MariaDB and Nginx)

* This example provides containers for:
    * PHP 7.4 (FPM)
    * MariaDB (latest)
    * Nginx (latest)
* Code in this directory will be mapped into the PHP container at `/var/www/html`
* Nginx will serve code from the `html` directory (i.e. `html` is the document root)
    * By default, `html/index.php` will provide you a `phpinfo()` report 
* You can access the website via **http://127.0.0.1:8080**
* If you want to use this example, it is suggested you copy the configuration into your own project and customise it accordingly


## Deploy Using Docker
### Requirement in your system
 - docker
 - docker-compose

### Usage

#### 1. Clone this Repository

```bash
git clone https://github.com/nc2U/ub
cd ub
```

#### 2. Copy docker-compose.yml (Security warning)

* There is a default password for MariaDB's root user specified in `docker/docker-compose.yml`
* You are strongly recommended to edit this line to replace `my-db-*` with a value known to you:

```bash
cd deploy
cp .docker-compose.yml docker-compose.yml
```

#### 3. Modify docker-compose.yml file

* Modify the values in the section below to the values you want to use.

```
    BASE_URL: http: //localhost:8080/
    ENCRYPTION_KEY: random-key
    DATABASE_HOST: mariadb
    DATABASE_NAME: my-db-name # mariadb env 와 일치
    DATABASE_USER: my-db-user # mariadb env 와 일치
    DATABASE_PASSWORD: my-db-password # mariadb env 와 일치
    ...
      
    MYSQL_DATABASE: my-db-name
    MYSQL_USER: my-db-user
    MYSQL_PASSWORD: my-db-password
    MYSQL_ROOT_PASSWORD: my-db-root-password
```

* This docker configuration has not been security hardened.  Expose it to public networks at your own risk!

#### 4. Build & Run

* Codeigniter3 configuration files are isolated for security reasons, so you need to take the following actions:

```bash
docker-compose up -d --build
```


## Deploy Using Kubernetes & Helm
### Requirement in your system
- kubernetes cluster
- helm
- nfs server(You can use one of the Kubernetes cluster nodes. However, the NFS server program must be installed and functioning properly).

### Usage

#### 1. Copy values_custom.yaml

```bash
cd deploy/helm
cp .values_custom.yaml values_custom.yaml
```

#### 2. Modify values_custom.yaml file

* Modify the values in the section below to the values you want to use.

```
global.nfsServerHost: '' # required -> nfs server host (ip)
global.nfsPath: '' # required -> nfs path (Path where the app will be installed)
global.baseUrl: '' # required ->  base url (ex: https://abc.com/)
global.dbHost: '' # database host (default: mariadb, If you haven't changed the mariadb image and service name, don't change it.)
global.dbName: '' # database name (default: ReleaseName - helm release app name ex: `helm install ub .` <- in this case ub)
global.dbUser: '' # database name (default: ReleaseName - helm release app name ex: `helm install ub .` <- in this case ub)
global.dbPassword: '' # root & user database password (default: secret)

# If you use ingress, modify the following values:
nginx.ingress.hosts.host[0]: '' # domain name (ex: abc.com)
nginx.ingress.tls.hosts[0]: '' # domain name (ex: abc.com, Modify when applying certificate)
nginx.ingress.tls.secretName[0]: '' # secret name (When applying a certificate, the secret where the certificate key is stored)
```

#### 3. Deploy

```bash
helm install ub . -f values_custom.yaml
```


#### 4. access

> https://abc.com/ (If you used Ingress, the domain) 

or

> http://xxx.xxx.xxx:8888/ (Port 8888 of the public IP of that node)