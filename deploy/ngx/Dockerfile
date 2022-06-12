FROM nginx:alpine
RUN apk add shadow;\
  usermod -u 1000 nginx;\
  groupmod -g 1000 nginx
COPY default.conf /etc/nginx/conf.d/
