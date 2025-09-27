<?php

require_once 'BaseModel.php';

class UserModel extends BaseModel
{

    public function findUserById($id)
    {
        $stmt = self::$_connection->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function findUser($keyword)
    {
        $stmt = self::$_connection->prepare('SELECT * FROM users WHERE user_name LIKE ? OR user_email LIKE ?');
        $like = "%$keyword%";
        $stmt->bind_param('ss', $like, $like);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Authentication user
     * @param $userName
     * @param $password
     * @return array
     */
    // public function auth($userName, $password) {
    //     $md5Password = md5($password);
    //     $sql = 'SELECT * FROM users WHERE name = "' . $userName . '" AND password = "'.$md5Password.'"';

    //     $user = $this->select($sql);
    //     return $user;
    // }

    public function auth($userName, $password)
    {
        $stmt = self::$_connection->prepare('SELECT * FROM users WHERE name = ? AND password = ?');
        $stmt->bind_param('ss', $userName, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }


    /**
     * Delete user by id
     * @param $id
     * @return mixed
     */
    public function deleteUserById($id)
    {
        $stmt = self::$_connection->prepare('DELETE FROM users WHERE id = ?');
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    /**
     * Update user
     * @param $input
     * @return mixed
     */
    public function updateUser($input)
    {
        $stmt = self::$_connection->prepare('UPDATE users SET name = ?, password = ? WHERE id = ?');
        $md5Password = md5($input['password']);
        $stmt->bind_param('ssi', $input['name'], $md5Password, $input['id']);
        return $stmt->execute();
    }

    /**
     * Insert user
     * @param $input
     * @return mixed
     */
    public function insertUser($input)
    {
        $stmt = self::$_connection->prepare('INSERT INTO users (name, password) VALUES (?, ?)');
        $md5Password = md5($input['password']);
        $stmt->bind_param('ss', $input['name'], $md5Password);
        return $stmt->execute();
    }

    /**
     * Search users
     * @param array $params
     * @return array
     */

    public function getUsers($params = [])
    {
        if (!empty($params['keyword'])) {
            $stmt = self::$_connection->prepare('SELECT * FROM users WHERE name LIKE ?');
            $like = "%{$params['keyword']}%";
            $stmt->bind_param('s', $like);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            $sql = 'SELECT * FROM users';
            $result = self::$_connection->query($sql);
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    }
}