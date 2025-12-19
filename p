Trang điều khiển trọng tài hiện tại nằm ở
resources/views/referee/matches/show.blade.php

Cần sửa lại file này tính năng theo file referee-match-control-vue.html vào file blade trên
→ không extend layout layouts.referee
→ sử dụng đúng design của referee-match-control-vue.html (có thể tinh chỉnh) và sửa lại logic.

Có thể cập nhật database, thêm bảng để lưu dữ liệu phát sinh trong trận (event, action…)

Khi bắt đầu trận:
→ sync state vào localStorage
→ sync lên API mỗi 10 event

Khi kết thúc trận:
→ đẩy toàn bộ dữ liệu lên API

Dựa vào thông tin match để xác định đấu đơn hay đấu đôi

File cũ resources/views/referee/matches/show.blade.php có logic cập nhật tỉ số các set
→ có thể cần chỉnh sửa lại

Logic Vue phải tuân theo luật Pickleball

Nếu trận đã kết thúc nhưng vẫn vào trang điều khiển:
→ hiển thị đội thắng
→ hiển thị tỉ số các set

- Cần reuse 1 số logic ở app/Http/Controllers/Front/RefereeController.php@updateScore khi kết thúc 
trận. hàm updateGroupStandingsAndAthleteStats cần cập nhật lại vì hiện tại chỉ hỗ trợ đánh đơn, 
cần cập nhật cho đánh đôi. 
- Api mới viết hãy viết trong web.php với middleware như route trọng tài cũ. 
- Sử dụng luật side-out