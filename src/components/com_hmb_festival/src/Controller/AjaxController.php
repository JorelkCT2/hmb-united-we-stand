<?php
namespace Hmb\Component\Festival\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Session\Session;

/**
 * Endpoints JSON del frontend.
 * index.php?option=com_hmb_festival&task=ajax.<accion>&format=json
 */
class AjaxController extends BaseController
{
    private function out(array $data): void
    {
        $app = Factory::getApplication();
        $app->setHeader('Content-Type', 'application/json', true);
        echo new JsonResponse($data);
        $app->close();
    }

    private function me(): int
    {
        $user = Factory::getApplication()->getIdentity();

        if (!$user || $user->guest) {
            $this->out(['ok' => false, 'error' => 'Sesión caducada. Vuelve a entrar.']);
        }

        return (int) $user->id;
    }

    private function token(): void
    {
        if (!Session::checkToken('request')) {
            $this->out(['ok' => false, 'error' => 'Token inválido. Recarga la página.']);
        }
    }

    /** Estado inicial: festivales activos + mis check-ins */
    public function state(): void
    {
        $me    = $this->me();
        $model = $this->getModel('Festival', 'Site');

        $model->purgeExpired();

        $this->out([
            'ok'        => true,
            'festivals' => $model->getFestivals(),
            'checkins'  => $model->getMyCheckins($me),
        ]);
    }

    public function checkin(): void
    {
        $this->token();
        $me    = $this->me();
        $input = $this->input;

        $result = $this->getModel('Festival', 'Site')->checkin(
            $me,
            $input->getInt('festival_id', 0),
            $input->getCmd('intent', 'colegas'),
            $input->getString('bio', ''),
            $input->getInt('adult', 0) === 1
        );

        $this->out($result);
    }

    public function checkout(): void
    {
        $this->token();
        $me = $this->me();

        $this->getModel('Festival', 'Site')->checkout($me, $this->input->getInt('festival_id', 0));
        $this->out(['ok' => true]);
    }

    public function people(): void
    {
        $me = $this->me();

        $this->out(
            $this->getModel('Festival', 'Site')->getPeople($this->input->getInt('festival_id', 0), $me)
        );
    }

    public function horn(): void
    {
        $this->token();
        $me = $this->me();

        $this->out(
            $this->getModel('Match', 'Site')->horn(
                $me,
                $this->input->getInt('user_id', 0),
                $this->input->getInt('festival_id', 0)
            )
        );
    }

    public function matches(): void
    {
        $me = $this->me();

        $this->out([
            'ok'      => true,
            'matches' => $this->getModel('Match', 'Site')->getMatches($me),
        ]);
    }

    public function messages(): void
    {
        $me = $this->me();

        $this->out(
            $this->getModel('Match', 'Site')->getMessages(
                $this->input->getInt('match_id', 0),
                $me,
                $this->input->getInt('since', 0)
            )
        );
    }

    public function send(): void
    {
        $this->token();
        $me = $this->me();

        $this->out(
            $this->getModel('Match', 'Site')->send(
                $this->input->getInt('match_id', 0),
                $me,
                $this->input->getString('body', '')
            )
        );
    }

    public function block(): void
    {
        $this->token();
        $me = $this->me();

        $this->out(
            $this->getModel('Match', 'Site')->block($me, $this->input->getInt('user_id', 0))
        );
    }

    public function report(): void
    {
        $this->token();
        $me = $this->me();

        $this->out(
            $this->getModel('Match', 'Site')->report(
                $me,
                $this->input->getInt('user_id', 0),
                $this->input->getInt('festival_id', 0),
                $this->input->getString('reason', '')
            )
        );
    }
}
