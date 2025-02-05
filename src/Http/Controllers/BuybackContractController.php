<?php

/*
This file is part of SeAT

Copyright (C) 2015 to 2020  Leon Jacobs

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

namespace H4zz4rdDev\Seat\SeatBuyback\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Seat\Web\Http\Controllers\Controller;
use H4zz4rdDev\Seat\SeatBuyback\Models\BuybackContract;

/**
 * Class BuybackContractController
 *
 * @package H4zz4rdDev\Seat\SeatBuyback\Http\Controllers
 */
class BuybackContractController extends Controller {

    /**
     * @return \Illuminate\View\View
     */
    public function getHome()
    {
        $contracts = BuybackContract::where('contractStatus', '=', 0)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('buyback::buyback_contract', [
            'contracts' => $contracts
        ]);
    }

    /**
     * @return mixed
     */
    public function getCharacterContracts () {

        //Todo Refactor contractIssuer to userID
        $openContracts = BuybackContract::where('contractStatus', '=', 0)
            ->where('contractIssuer', '=', Auth::user()->name)
            ->orderBy('created_at', 'DESC')
            ->get();

        $closedContracts = BuybackContract::where('contractStatus', '=', 1)
            ->where('contractIssuer', '=', Auth::user()->name)
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('buyback::buyback_my_contracts',  [
            'openContracts' => $openContracts,
            'closedContracts' => $closedContracts
        ]);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function insertContract(Request $request) {

        $request->validate([
            'contractId' => 'required',
            'contractData' => 'required'
        ]);

        $contract = new BuybackContract();
        $contract->contractId = $request->get('contractId');
        $contract->contractIssuer = Auth::user()->name;
        $contract->contractData = $request->get('contractData');
        $contract->save();

        return redirect()->route('buyback.character.contracts')
            ->with('success', trans('buyback::global.contract_success_submit', ['id' => $request->get('contractId')]));
    }

    /**
     * @param Request $request
     * @param string $contractId
     * @return mixed
     */
    public function deleteContract (Request $request, string $contractId)
    {
        if(!$request->isMethod('get') || empty($contractId))
        {
            return redirect()->back()
                ->with(['error' => trans('buyback::global.error')]);
        }

        BuybackContract::destroy($contractId);

        return redirect()->back()
            ->with('success', trans('buyback::global.contract_success_deleted', ['id' => $contractId]));
    }

    /**
     * @param Request $request
     * @param string $contractId
     * @return mixed
     */
    public function succeedContract (Request $request, string $contractId)
    {
        if(!$request->isMethod('get') || empty($contractId))
        {
            return redirect()->back()
                ->with(['error' => trans('buyback::global.error')]);
        }

        $contract = BuybackContract::where('contractId', '=', $contractId)->first();
        if(!$contract->contractStatus) {

            $contract->contractStatus = 1;
            $contract->save();

            return redirect()->back()
                ->with('success', trans('buyback::global.contract_success_succeeded', ['id' => $contractId]));
        }

        return redirect()->back()
            ->with(['error' => trans('buyback::global.error')]);
    }
}
