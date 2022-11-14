# Readme

## Install
- Copy to nginx folder /var/www/html/
```shell
cd /var/www/html/
git clone https://github.com/CAFECA-IO/iSunTVMall-Web
```

- add .env
```shell
sudo vi .env
```
```shell
app_namespace=wstmart
```

- import db schema
```shell
(use Tableplus)
```

- Setup runtime folder
```shell
mkdir runtime
chmod 0777 runtime
```
