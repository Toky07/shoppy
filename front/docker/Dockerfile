# syntax=docker/dockerfile:1.7

FROM node:22-alpine AS development

WORKDIR /app

COPY package.json package-lock.json ./

RUN --mount=type=cache,target=/root/.npm \
    npm config set fetch-retries 5 \
    && npm config set fetch-retry-mintimeout 20000 \
    && npm config set fetch-retry-maxtimeout 120000 \
    && npm ci --no-audit --no-fund --prefer-offline

COPY --chmod=755 docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint

EXPOSE 5173

ENTRYPOINT ["docker-entrypoint"]
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0", "--port", "5173", "--strictPort"]

FROM node:22-alpine AS build

WORKDIR /app

COPY package.json package-lock.json ./

RUN --mount=type=cache,target=/root/.npm \
    npm config set fetch-retries 5 \
    && npm config set fetch-retry-mintimeout 20000 \
    && npm config set fetch-retry-maxtimeout 120000 \
    && npm ci --no-audit --no-fund --prefer-offline

COPY . .

ARG VITE_API_URL=/api
ENV VITE_API_URL=$VITE_API_URL

RUN npm run build-only

FROM nginx:1.27-alpine AS production

RUN apk add --no-cache openssl \
    && mkdir -p /etc/nginx/certs \
    && openssl req -x509 -nodes -days 3650 -newkey rsa:2048 \
        -keyout /etc/nginx/certs/server.key \
        -out /etc/nginx/certs/server.crt \
        -subj "/CN=localhost"

COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/security-headers.conf /etc/nginx/snippets/security-headers.conf
COPY docker/uploads-headers.conf /etc/nginx/snippets/uploads-headers.conf
COPY --from=build /app/dist /usr/share/nginx/html

EXPOSE 80 443

HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=5 \
    CMD wget --no-check-certificate -qO- https://127.0.0.1/ >/dev/null || exit 1
