<?php

namespace ElementVip\Server\Http\Controllers;

use ElementVip\Component\Card\Builders\DefaultBuilder;
use ElementVip\Component\Card\Models\Card;
use ElementVip\Component\Card\Repository\CardRepository;
use ElementVip\Server\Transformers\CardTransformer;
use ElementVip\Component\User\Models\User;
use Illuminate\Support\Facades\Log;

class CardController extends Controller
{
    protected $cardRepository;

    public function __construct(CardRepository $cardRepository)
    {
        $this->cardRepository = $cardRepository;
    }

    public function myCard()
    {
        $card = $this->cardRepository->getCardByUserId(request()->user()->id)->first();
        if ($card) {
            return $this->response()->item($card, new CardTransformer());
        }
        return $this->api([], false, 200, 'user do not have to receive card.');
    }

    public function myCardBarCode()
    {
        $br = $this->cardRepository->getCardBrByUserId(request()->user()->id);
        if ($br && $br !== '') {
            return $this->api(['base64' => 'data:image/png;base64,' . $br]);
        }
        return $this->response()->noContent();
    }

    public function store(DefaultBuilder $builder)
    {
        User::where(['id' => request()->user()->id])->update(['birthday' => request('birthday')]);

        $user = request()->user();

        if ($card = $user->card) {
            $card->fill(request()->except('access_token'));
            $card->save();
        } else {
            $card = Card::create(array_merge(request()->all(), ['number' => $builder->generateNumber(),'user_id'=>$user->id]));
        }

        return $this->response()->item($card, new CardTransformer());
    }

    


}
