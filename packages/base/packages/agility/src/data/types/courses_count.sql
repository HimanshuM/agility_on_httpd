SELECT
	count(enr.enr_id) AS count,
	c.name,
	c.sis_id
FROM
	user_enrollment AS enr
INNER JOIN
	course AS c
	ON c.course_id = enr.course_id
INNER JOIN
	subs AS s
	ON s.subs_id = enr.subs_id
WHERE
	DATE(s.end_date) > CURRENT_DATE
	AND enr.status = 'active'
	AND (
		s.status = 'active'
		OR s.status = 'pending'
		OR s.status = 'alumni'
		OR s.status = 'frozen'
		OR s.status = 'expired'
	)
	AND c.sis_id IS NOT NULL
GROUP BY
	c.course_id;