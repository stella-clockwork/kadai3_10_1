<?php
    ini_set('default_charset', 'UTF-8');
    require_once(dirname(__FILE__) . '/util.php');
    require_once(dirname(__FILE__) . '/SQL_util.php');
    require_once(dirname(__FILE__) . '/myexception.php');
    
    
    function create_table($db, $table_name) {
        try {
            $db->beginTransaction();
            
            table_name_regist_validation($db, $table_name);
            $sql =   "CREATE TABLE IF NOT EXISTS `" . "$table_name" . "`"
                    ."("
                    . "`id`        INT auto_increment primary key,"
                    . "`name`      VARCHAR(60),"
                    . "`comment`   TEXT,"
                    . "`post_date` DATETIME,"
                    . "`password`  TEXT"
                    .");";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $db->commit();
        } catch (PDOException $PDOe) {
            $db->rollback();
            throw new MyException(
                "failed to create table {$table_name}: ".$PDOe->getMessage(),
                 MyException::DEAL_WARN);
        } catch (MyException $Me) {
            $db->rollback();
            throw $Me;
        }
    }
    
    
    function insert_element($db, $table_name, $name, $comment, $content_type) {
        try {
            $db->beginTransaction();
            
            table_name_call_validation($db, $table_name);
            $sql = "INSERT INTO " . "$table_name"
                  ." (id, name, comment, post_date, content_type)"
                  ." VALUES ('', :name, :comment, :post_date, :content_type)";
            $stmt = $db->prepare($sql);
            
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt->bindValue(':post_date', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->bindValue(':content_type', $content_type, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($content_type !== NO_CONTENT) {
                $used_id = $db->lastInsertId();
                $sql = "UPDATE " . "$table_name"
                      ." SET content =:content, mime_type =:mime_type"
                      ." WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':id', $used_id, PDO::PARAM_INT);
                $stmt->bindValue(':content', 
                           file_get_contents($_FILES['file']['tmp_name']), 
                           PDO::PARAM_STR);
                $stmt->bindValue(':mime_type', $_FILES['file']['type'],
                                 PDO::PARAM_STR);
                $stmt->execute();
            }
            $db->commit();
        } catch (PDOException $PDOe) {
            $db->rollback();
            throw new MyException(
                "failed to insert {$used_id}th post: ".$PDOe->getMessage(),
                 MyException::DEAL_WARN);
        } catch (MyException $Me) {
            $db->rollback();
            throw $Me;
        }
    }
    
    
    function delete_element($db, $table_name, $delete_id) {
        try {
            $db->beginTransaction();
            
            table_name_call_validation($db, $table_name);
            
            $stmt = get_one_element($db, $table_name, $delete_id); 
            $row = $stmt->fetch();
            if ($row['name'] !== $_SESSION['username']) {
                throw new MyException('not your comment',
                                      MyException::DEAL_FATAL);
            }
            
            $sql = "DELETE FROM "."$table_name"." where id = :delete_id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':delete_id', $delete_id, PDO::PARAM_INT);
            $stmt->execute();
            $db->commit();
        } catch (PDOException $PDOe) {
            $db->rollback();
            throw new MyException(
                "failed to delete {$delete_id}th post: ".$PDOe->getMessage(),
                 MyException::DEAL_WARN);
        } catch (MyException $Me) {
            $db->rollback();
            throw $Me;
        }
    }
    
    
    function update_element($db, $table_name, $update_id, $comment) {
        try {
            $db->beginTransaction();
            
            table_name_call_validation($db, $table_name);
            $stmt = get_one_element($db, $table_name, $update_id); 
            $row = $stmt->fetch();
            if ($row['name'] !== $_SESSION['username']) {
                throw new MyException('not your comment',
                                      MyException::DEAL_FATAL);
            }
            
            $sql = "UPDATE " . "$table_name"
                  ." SET comment =:comment,"
                  ." post_date =:post_date"
                  ." WHERE id = :update_id";
            $stmt = $db->prepare($sql);
            
            $stmt->bindValue(':comment', $comment, PDO::PARAM_STR);
            $stmt->bindValue(':post_date', date("Y-m-d H:i:s"), PDO::PARAM_STR);
            $stmt->bindValue(':update_id', $update_id, PDO::PARAM_INT);
            $stmt->execute();
            $db->commit();
        } catch (PDOException $PDOe) {
            $db->rollback();
            throw new MyException(
                "failed to update {$update_id}th post: ".$PDOe->getMessage(),
                 MyException::DEAL_WARN);
        } catch (MyException $Me) {
            $db->rollback();
            throw $Me;
        }
    }
?>