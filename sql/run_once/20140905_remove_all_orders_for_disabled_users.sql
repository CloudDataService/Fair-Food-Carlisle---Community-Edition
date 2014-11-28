/* Fixes deleted accounts recurring orders showing up in picking lists when they were previously cancelled. */

UPDATE orderitem
SET oi_status = "Cancelled"
WHERE oi_u_id in
(
    select u_id from
    (
        select u_id
        from user
        where u_status = 'Removed'
    ) as oi_u_id
);
