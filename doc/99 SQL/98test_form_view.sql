TRUNCATE TABLE `t_fv_md`;
INSERT INTO `t_fv_md` (id, parent_id, prop_name, prop_value, show_order) VALUES
('01', NULL, 'view_name', '销售订单', 1),
('0101', '01', 'fid', '2028', 0),
('0102', '01', 'title', '销售订单', 0),
('0103', '01', 'view_type', '2', 0),
('02', NULL, 'view_name', '仓库', 2),
('0201', '02', 'fid', '1003', 0),
('0202', '02', 'title', '仓库', 0),
('0203', '02', 'view_type', '1', 0),
('0204', '02', 'tool_bar_id', '020301', 0),
('02040101', '020401', 'button_text', '新增仓库', 1),
('02040102', '020401', 'button_text', '编辑仓库', 2),
('02040103', '020401', 'button_text', '删除仓库', 3);
