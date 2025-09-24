<?php

require_once 'BaseModel.php';

class UserModel extends BaseModel
{

    public function findUserById($id)
    {
        $sql = 'SELECT * FROM users WHERE id = ' . $id;
        $user = $this->select($sql);

        return $user;
    }

    public function findUser($keyword)
    {
        $sql = 'SELECT * FROM users WHERE user_name LIKE %' . $keyword . '%' . ' OR user_email LIKE %' . $keyword . '%';
        $user = $this->select($sql);

        return $user;
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
        $hashPassword = md5($password);
        $sql = 'SELECT * FROM users WHERE name = "' . $userName . '" AND password = "' . $hashPassword . '"';
        return $this->select($sql);
    }


    /**
     * Delete user by id
     * @param $id
     * @return mixed
     */
    public function deleteUserById($id)
    {
        $sql = 'DELETE FROM users WHERE id = ' . $id;
        return $this->delete($sql);

    }

    /**
     * Update user
     * @param $input
     * @return mixed
     */
    public function updateUser($input)
    {
        $sql = 'UPDATE users SET 
                 name = "' . mysqli_real_escape_string(self::$_connection, $input['name']) . '", 
                 password="' . md5($input['password']) . '"
                WHERE id = ' . $input['id'];

        $user = $this->update($sql);

        return $user;
    }

    /**
     * Insert user
     * @param $input
     * @return mixed
     */
    public function insertUser($input)
    {
        $name = $input['name'] ?? '';
        $password = $input['password'] ?? '';
        $fullname = $input['fullname'] ?? ''; 
        $email = $input['email'] ?? ''; 
        $type = $input['type'] ?? '';
        $vesion = $input['vesion'] ?? 0;// <-- tránh undefined key

        $sql = "INSERT INTO `app_web1`.`users` (`name`, `password`, `fullname`, `email`, `type`, `version`) VALUES (" .
            "'" . $this->escape($name) . "', '" . md5($password) . "', '" . $this->escape($fullname) . "', '" . $this->escape($email) . "', '" . $this->escape($type) . "', '" . $this->escape($vesion) . "')";

        $user = $this->insert($sql);
        return $user;
    }


    /**
     * Search users
     * @param array $params
     * @return array
     */
    public function getUsers($params = [])
    {
        //Keyword
        if (!empty($params['keyword'])) {
            $sql = 'SELECT * FROM users WHERE name LIKE "%' . $params['keyword'] . '%"';

            //Keep this line to use Sql Injection
            //Don't change
            //Example keyword: abcef%";TRUNCATE banks;##
            $users = self::$_connection->multi_query($sql);

            //Get data
            $users = $this->query($sql);
        } else {
            $sql = 'SELECT * FROM users';
            $users = $this->select($sql);
        }

        return $users;
    }
}