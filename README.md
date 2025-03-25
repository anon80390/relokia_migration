# Розгортання проєкту з Docker

## Вимоги
- Docker
- Docker Compose

## Кроки для розгортання

1. Клонуйте репозиторій:
   ```bash
   git clone https://github.com/anon80390/relokia_task.git
   cd relokia_task

2. Побудуйте та запустіть контейнери:
   ```bash
   docker-compose up --build -d
   
3. Перевірте, що контейнери працюють:
   ```bash
   docker ps

4. Доступ до додатку:
   ```bash
   http://<ваш_сервер>
