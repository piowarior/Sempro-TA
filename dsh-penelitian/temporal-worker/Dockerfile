FROM golang:1.23-alpine AS builder

WORKDIR /app

RUN apk add --no-cache git

COPY . .

RUN go mod tidy
RUN CGO_ENABLED=0 GOOS=linux go build -o temporal-worker main.go

FROM alpine:latest

RUN apk --no-cache add ca-certificates tzdata
WORKDIR /root/

COPY --from=builder /app/temporal-worker .

EXPOSE 9000

CMD ["./temporal-worker"]
