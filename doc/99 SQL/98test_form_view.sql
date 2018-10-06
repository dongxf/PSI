TRUNCATE TABLE `t_fv_md`;
INSERT INTO `t_fv_md` (id, parent_id, prop_name, prop_value, show_order) VALUES
('01', NULL, 'view_name', '销售订单', 1),
('0101', '01', 'fid', '2028', 0),
('0102', '01', 'title', '销售订单', 0),
('02', NULL, 'view_name', '仓库', 2),
('0201', '02', 'fid', '1003', 0),
('0202', '02', 'title', '仓库', 0);
