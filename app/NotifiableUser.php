<?php
/**
 * Created by IntelliJ IDEA.
 * User: pangratioscosma
 * Date: 15/07/2019
 * Time: 1:01 PM
 */

namespace App;

use Illuminate\Notifications\Notifiable;

/**
 *
 * Use this class to define a 'user' at runtime,
 * with custom email / phone number.
 *
 * Class NotifiableUser
 * @package App
 *
 * @property string email
 * @property string phone_number
 */
class NotifiableUser
{
    use Notifiable;
    /**
     * @var User
     */
    public $user;
    /**
     * @var string
     */
    public $email;
    /**
     * @var string
     */
    public $phone_number;

    /**
     * NotifiableUser constructor.
     *
     * @param User $user
     * @param string $email
     * @param string $phoneNumber
     */
    public function __construct(User $user, string $email = null, string $phoneNumber = null)
    {
        $this->user         = $user;
        $this->email        = $email ?? $user->email;
        $this->phone_number = $phoneNumber ?? $user->getPhone();
    }
}
