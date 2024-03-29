<?php

namespace App\Repositories;

use App\Interfaces\ClientRepositoryInterface;
use App\Models\Client;

class ClientRepository implements ClientRepositoryInterface
{
    public function getAllClients()
    {
        $clients = Client::orderBy('name', 'asc')
            ->orderBy('province', 'asc')
            ->orderBy('regency', 'asc')
            ->orderBy('district', 'asc')
            ->orderBy('subdistrict', 'asc')
            ->get();

        return $clients;
    }

    public function create(array $data)
    {
        $client = new Client();
        $client->code = $data['code'];
        $client->name = $data['name'];
        $client->province = $data['province'];
        $client->regency = $data['regency'];
        $client->district = $data['district'];
        $client->subdistrict = $data['subdistrict'];
        $client->address = $data['address'];
        $client->phone = $data['phone'];
        $client->email = $data['email'];
        $client->save();

        return $client;
    }

    public function getClientById(string $id)
    {
        $client = Client::find($id);

        return $client;
    }

    public function update(array $data, string $id)
    {
        $client = Client::find($id);
        $client->code = $data['code'];
        $client->name = $data['name'];
        $client->province = $data['province'];
        $client->regency = $data['regency'];
        $client->district = $data['district'];
        $client->subdistrict = $data['subdistrict'];
        $client->address = $data['address'];
        $client->phone = $data['phone'];
        $client->email = $data['email'];
        $client->save();

        return $client;
    }

    public function delete(string $id)
    {
        $client = Client::find($id);
        $client->delete();

        return $client;
    }

    public function generateCode(int $tryCount): string
    {
        $count = Client::withTrashed()->count() + 1 + $tryCount;
        $code = 'CL'.str_pad($count, 5, '0', STR_PAD_LEFT);

        return $code;
    }

    public function isUniqueCode(string $code, $exceptId = null): bool
    {
        $query = Client::where('code', $code);
        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->doesntExist();
    }
}
