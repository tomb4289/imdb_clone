UPDATE movies
SET description = LEFT(description, 500)
WHERE LENGTH(description) > 500;