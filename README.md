# MySQL DBO

MySQL DBO is a simple wrapper around PHP's MySQLi, used internally at TMDDS. It was written as an exercise in PHP's dynamic capabilities and to be instructive to the TMDDS staff.

## Extremely Contrived Examples

    $db = new TMDDS\Database($mysqli);
    try {
        $bar = $db->query_value('SELECT bar FROM settings WHERE id = 1');
        $rows = $db->query('SELECT * FROM foo WHERE bar = ?, $bar);
        foreach ($rows as $row) {
            if ($row['baz'] == 'bing') {
                $db->exec('UPDATE foo SET biff = ? WHERE id = ?', 'boff', $row['id']);
            }
        }
    } catch (\Exception $e) {
        // Do something with $e
    }

Of course, the entirety of this could be replaced with a single SQL query, but then I couldn't demonstrate MySQL DBO.


