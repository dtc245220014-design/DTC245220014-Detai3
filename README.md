# DTC245220014-Detai3 — Đề 3: Website Quảng bá Sản phẩm (WordPress)

Sinh viên: Nguyễn Minh Đức — Lớp K23A (An toàn thông tin)

## Kiến trúc (Commit 1 + Commit 2)

```
Internet/User
     |  :80 / :443
 [Nginx Reverse Proxy + SSL]  (frontend_net)
     |               \
     v                v
 [WordPress]      [phpMyAdmin]   (frontend_net + backend_net)
     |                 |
     +--------+--------+
              v
        [MySQL 8.0]    (backend_net - internal)

 [Prometheus :9090] <--scrape 5s-- cAdvisor :8080 (container metrics)
         ^                        Node-Exporter :9100 (host metrics)
         |                                ^
 [Grafana :3000] -------------------------+
```

## Chuẩn bị SSL tự ký (PowerShell)

```powershell
New-Item -ItemType Directory -Force -Path .\nginx\ssl
openssl req -x509 -nodes -days 365 -newkey rsa:2048 `
  -keyout .\nginx\ssl\nginx.key -out .\nginx\ssl\nginx.crt `
  -subj "/C=VN/ST=HaNoi/L=HaNoi/O=NguyenMinhDuc_K23A_ATTT/CN=localhost"
```

## Kiểm tra cấu hình Nginx

```powershell
docker compose run --rm --no-deps nginx nginx -t
```

## Khởi chạy hệ thống

```powershell
docker compose up -d
docker compose ps
docker compose logs -f
```

## Kiểm tra truy cập

- Website WordPress: https://localhost  (bỏ qua cảnh báo Self-Signed Certificate)
- phpMyAdmin: https://localhost/phpmyadmin/ (đăng nhập bằng MYSQL_USER / MYSQL_PASSWORD trong `.env`)

## Dừng / dọn dẹp

```powershell
docker compose down          # dừng nhưng giữ dữ liệu DB
docker compose down -v       # dừng và xóa toàn bộ dữ liệu DB
```

## HỆ THỐNG GIÁM SÁT (Commit 2)

### Các service giám sát

| Service | Image | Cổng | Vai trò |
|---|---|---|---|
| prometheus | prom/prometheus:v2.48.0 | 9090 | Thu & lưu metrics, scrape mỗi 5s |
| cadvisor | ghcr.io/google/cadvisor:v0.60.5 | (nội bộ 8080) | Metrics từng container (CPU/RAM/Network/IO) |
| node-exporter | prom/node-exporter:v1.7.0 | 9100 | Metrics máy host (CPU/RAM/đĩa/mạng) |
| grafana | grafana/grafana:10.2.2 | 3000 | Dashboard trực quan, volume `grafana_data` |

> **Lưu ý version cAdvisor:** đề bài nêu `gcr.io/cadvisor/cadvisor:v0.47.0`,
> nhưng Docker Desktop 29 dùng containerd-snapshotter nên bản v0.47.0 báo lỗi
> `failed to identify the read-write layer ID` và **không thấy container nào**
> (xem google/cadvisor#3643, fix ở PR #3709 phát hành từ v0.54.0). Do đó dùng
> image chính thức mới `ghcr.io/google/cadvisor:v0.60.5` để dashboard có số liệu thật.

### Kiểm tra Prometheus

- Mở http://localhost:9090 → mục **Status → Targets**: 3 target `prometheus`,
  `cadvisor`, `node-exporter` đều trạng thái **UP**.
- Mở http://localhost:9090/graph nhập PromQL (xem danh sách mẫu bên dưới).

### Hướng dẫn Grafana (http://localhost:3000)

1. Đăng nhập: user `admin`, mật khẩu `admin` → đổi mật khẩu khi được yêu cầu (có thể Skip).
2. **Thêm Datasource Prometheus:**
   - Menu (góc trái) → **Connections → Data sources → Add data source → Prometheus**.
   - URL: `http://prometheus:9090`
   - Scroll xuống bấm **Save & test** → hiệngreen *"Data source is working"*.
3. **Import Dashboard mẫu giám sát Docker:**
   - Menu → **Dashboards → Import → Import dashboard**.
   - Nhập Dashboard ID `14282` (Docker Containers Monitoring) → bấm **Load** →
     chọn datasource Prometheus vừa tạo → **Import**.
   - Lặp lại với ID `893` (Docker Monitoring) nếu muốn thêm.
4. Mở dashboard vừa import → các panel CPU, Memory, Network, I/O của container
   WordPress/Nginx/MySQL hiển thị số liệu thời gian thực.

### 4 câu lệnh PromQL mẫu (kiểm tra trên http://localhost:9090/graph)

```promql
# 1. CPU usage (%) của container wordpress và nginx
sum(rate(container_cpu_usage_seconds_total{name=~"dtc3_wordpress|dtc3_nginx"}[1m])) by (name) * 100

# 2. Memory usage (bytes) của wordpress, nginx, mysql
container_memory_usage_bytes{name=~"dtc3_wordpress|dtc3_nginx|dtc3_mysql"}

# 3. CPU usage của MÁY HOST (node-exporter)
100 - (avg(rate(node_cpu_seconds_total{mode="idle"}[2m])) * 100)

# 4. 3 container tốn RAM nhất + băng thông nhận của nginx
topk(3, sum(container_memory_usage_bytes{name=~"dtc3.*"}) by (name))
sum(rate(container_network_receive_bytes_total{name="dtc3_nginx"}[2m]))
```

Kết quả đo thực tế trên máy (ví dụ):
`CPU% dtc3_mysql = 0.947`, `MEM dtc3_mysql = 362.1 MB`,
`MEM dtc3_wordpress = 154.5 MB`, `MEM dtc3_nginx = 13 MB`,
`HOST_CPU_USAGE% = 3.43`.

## Git — Commit 1

```powershell
git add .
git commit -m "Commit 1: Deploy wordpress app with Nginx reverse proxy and HTTPS"
git push -u origin main
```

## Git — Commit 2

```powershell
docker compose up -d
git add .
git commit -m "Commit 2: Add Prometheus metrics and Grafana monitoring"
git push
```
