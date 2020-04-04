<?php
/**
 * Created by IntelliJ IDEA.
 * User: pangratioscosma
 * Date: 15/07/2019
 * Time: 1:01 PM
 */

namespace App;

use Illuminate\Notifications\AnonymousNotifiable;

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
class NotifiableUser extends AnonymousNotifiable
{
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

        if ($this->email) {
            $this->route('mail', $this->email);
        }

        if ($this->phone_number) {
            $this->route('twilio', $this->phone_number);
        }
    }

    public function getKey()
    {
        return $this->user->id ?? 0;
    }


}
