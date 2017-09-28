<?php
namespace app\Tasks;

use Server\CoreBase\Task;

/**
 * @author weihan
 * @datetime 2016年11月22日下午5:42:28
 *
 */
class UserTask extends Task
{
    /**
     * 发送邮件
     * @param string $toemail
     * @param string $subject
     * @param string $message
     * @param string $sitename
     *
     * @author weihan
     * @datetime 2016年11月22日下午5:46:07
     */
    public function sendEmail($toemail, $subject, $message, $sitename)
    {
        sendmail($toemail, $subject, $message, $sitename);
    }

}