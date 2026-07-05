<?php

namespace App\Http\Controllers;
use App\Models\Router;
use Illuminate\Http\Request;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;
use App\Services\MikroTikService;
class RouterController extends Controller
{
    protected $mikrotik;
    public function __construct(MikroTikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }
    /*
    |--------------------------------------------------------------------------
    | DAFTAR ROUTER
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $routers = Router::all();
        return view('router.index', compact('routers'));
    }
    public function create()
    {
        return view('router.create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'nama_router' => 'required',
            'ip_router'   => 'required',
            'api_port'    => 'required|numeric',
            'username'    => 'required',
            'password'    => 'required',
            'status'      => 'required',
        ]);
        Router::create([
            'nama_router'    => $request->nama_router,
            'ip_router'      => $request->ip_router,
            'api_port'       => $request->api_port,
            'username'       => $request->username,
            'password'       => $request->password,
            'lokasi'         => $request->lokasi,
            'versi_routeros' => $request->versi_routeros,
            'ssl'            => $request->has('ssl'),
            'status'         => $request->status,
        ]);
        return redirect()
            ->route('router.index')
            ->with('success', 'Router berhasil ditambahkan.');
    }
    public function show(Router $router)
    {
        //
    }
    public function edit(Router $router)
    {
        return view('router.edit', compact('router'));
    }
    public function update(Request $request, Router $router)
    {
        $request->validate([
            'nama_router' => 'required',
            'ip_router'   => 'required',
            'api_port'    => 'required|numeric',
            'username'    => 'required',
            'password'    => 'required',
            'status'      => 'required',
        ]);
        $router->update([
            'nama_router'    => $request->nama_router,
            'ip_router'      => $request->ip_router,
            'api_port'       => $request->api_port,
            'username'       => $request->username,
            'password'       => $request->password,
            'lokasi'         => $request->lokasi,
            'versi_routeros' => $request->versi_routeros,
            'ssl'            => $request->has('ssl'),
            'status'         => $request->status,
        ]);
        return redirect()
            ->route('router.index')
            ->with('success', 'Router berhasil diperbarui.');
    }
    /*
    |--------------------------------------------------------------------------
    | TEST KONEKSI
    |--------------------------------------------------------------------------
    */
    public function test($id)
    {
        $router = Router::findOrFail($id);
        try {
            $client = new Client(new Config([
                'host' => $router->ip_router,
                'user' => $router->username,
                'pass' => $router->password,
                'port' => $router->api_port,
            ]));
            $query = new Query('/system/resource/print');
            $result = $client->query($query)->read();
            return redirect()
                ->route('router.index')
                ->with(
                    'success',
                    'Berhasil terhubung : ' .
                    $result[0]['board-name'] .
                    ' | RouterOS ' .
                    $result[0]['version']
                );
        } catch (\Exception $e) {
            return redirect()
                ->route('router.index')
                ->with('error', $e->getMessage());
        }
    }
    /*
    |--------------------------------------------------------------------------
    | DAFTAR PPP SECRET
    |--------------------------------------------------------------------------
    */
    public function pppSecret($id)
    {
        $router = Router::findOrFail($id);
        try {
            $client = new Client(new Config([
                'host' => $router->ip_router,
                'user' => $router->username,
                'pass' => $router->password,
                'port' => $router->api_port,
            ]));
            $query = new Query('/ppp/secret/print');
            $query->equal('.proplist', '.id,name,password,service,profile,disabled');
            $secrets = $client->query($query)->read();
            return view(
                'router.ppp-secret',
                compact('router', 'secrets')
            );
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
    /*
    |--------------------------------------------------------------------------
    | PPP PROFILE
    |--------------------------------------------------------------------------
    */
    public function pppProfile($id)
    {
        $router = Router::findOrFail($id);
        try {
            $client = new Client(new Config([
                'host' => $router->ip_router,
                'user' => $router->username,
                'pass' => $router->password,
                'port' => $router->api_port,
            ]));
            $query = new Query('/ppp/profile/print');
            $query->equal(
                '.proplist',
                '.id,name,local-address,remote-address,rate-limit,only-one'
            );
            $profiles = $client->query($query)->read();
            return view(
                'router.ppp-profile',
                compact('router', 'profiles')
            );
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

        /*
    |--------------------------------------------------------------------------
    | FORM TAMBAH PPP SECRET
    |--------------------------------------------------------------------------
    */
    public function createSecret($id)
    {
        $router = Router::findOrFail($id);
        try {
            $client = new Client(new Config([
                'host' => $router->ip_router,
                'user' => $router->username,
                'pass' => $router->password,
                'port' => $router->api_port,
            ]));
            // Ambil daftar profile
            $query = new Query('/ppp/profile/print');
            $query->equal('.proplist', 'name');
            $result = $client->query($query)->read();
            $profiles = [];
            foreach ($result as $item) {
                if (isset($item['name'])) {
                    $profiles[] = [
                        'name' => $item['name']
                    ];
                }
            }
            return view(
                'router.create-secret',
                compact('router', 'profiles')
            );
        } catch (\Exception $e) {
            return redirect()
                ->route('router.pppsecret', $router->id)
                ->with('error', $e->getMessage());
        }
    }
    /*
    |--------------------------------------------------------------------------
    | SIMPAN PPP SECRET
    |--------------------------------------------------------------------------
    */
    public function storeSecret(Request $request, $id)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
            'service'  => 'required',
            'profile'  => 'required',
        ]);
        $router = Router::findOrFail($id);
        try {
            $client = new Client(new Config([
                'host' => $router->ip_router,
                'user' => $router->username,
                'pass' => $router->password,
                'port' => $router->api_port,
            ]));
            $query = new Query('/ppp/secret/add');
            $query->equal('name', $request->username);
            $query->equal('password', $request->password);
            $query->equal('service', $request->service);
            $query->equal('profile', $request->profile);
            $client->query($query)->read();
            return redirect()
                ->route('router.pppsecret', $router->id)
                ->with('success', 'PPP Secret berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /*
|--------------------------------------------------------------------------
| FORM EDIT PPP SECRET
|--------------------------------------------------------------------------
*/
public function editSecret($id, $username)
{
    $router = Router::findOrFail($id);
    try {
        $client = new Client(new Config([
            'host' => $router->ip_router,
            'user' => $router->username,
            'pass' => $router->password,
            'port' => $router->api_port,
        ]));
        // Ambil semua secret
        $query = new Query('/ppp/secret/print');
        $query->equal('.proplist', '.id,name,password,service,profile,disabled');
        $result = $client->query($query)->read();
        $secret = null;
        foreach ($result as $item) {
            if (($item['name'] ?? '') == $username) {
                $secret = $item;
                break;
            }
        }
        if (!$secret) {
            return redirect()
                ->route('router.pppsecret', $router->id)
                ->with('error', 'PPP Secret tidak ditemukan.');
        }
        // Ambil daftar profile
        $query = new Query('/ppp/profile/print');
        $query->equal('.proplist', 'name');
        $result = $client->query($query)->read();
        $profiles = [];
        foreach ($result as $item) {
            if (isset($item['name'])) {
                $profiles[] = [
                    'name' => $item['name']
                ];
            }
        }
        return view(
            'router.edit-secret',
            compact('router', 'secret', 'profiles')
        );
    } catch (\Exception $e) {
        return redirect()
            ->route('router.pppsecret', $router->id)
            ->with('error', $e->getMessage());
    }
}
/*
|--------------------------------------------------------------------------
| UPDATE PPP SECRET
|--------------------------------------------------------------------------
*/
public function updateSecret(Request $request, $id, $secret)
{
    $request->validate([
        'username' => 'required',
        'password' => 'required',
        'service'  => 'required',
        'profile'  => 'required',
        'disabled' => 'required',
    ]);
    $router = Router::findOrFail($id);
    try {
        $client = new Client(new Config([
            'host' => $router->ip_router,
            'user' => $router->username,
            'pass' => $router->password,
            'port' => $router->api_port,
        ]));
        // Cari .id berdasarkan username lama
        $query = new Query('/ppp/secret/print');
        $query->equal('.proplist', '.id,name');
        $result = $client->query($query)->read();
        $secretId = null;
        foreach ($result as $item) {
            if (($item['name'] ?? '') == $secret) {
                $secretId = $item['.id'];
                break;
            }
        }
        if (!$secretId) {
            return back()->with('error', 'PPP Secret tidak ditemukan.');
        }
        // Update Secret
        $query = new Query('/ppp/secret/set');
        $query->equal('.id', $secretId);
        $query->equal('name', $request->username);
        $query->equal('password', $request->password);
        $query->equal('service', $request->service);
        $query->equal('profile', $request->profile);
        $query->equal('disabled', $request->disabled);
        $client->query($query)->read();
        return redirect()
            ->route('router.pppsecret', $router->id)
            ->with('success', 'PPP Secret berhasil diupdate.');
    } catch (\Exception $e) {
        return back()
            ->withInput()
            ->with('error', $e->getMessage());
    }
}

/*
|--------------------------------------------------------------------------
| HAPUS PPP SECRET
|--------------------------------------------------------------------------
*/

public function deleteSecret($id, $secret)
{
    $router = Router::findOrFail($id);

    try {

        $config = new Config([
            'host' => $router->ip_router,
            'user' => $router->username,
            'pass' => $router->password,
            'port' => $router->api_port,
        ]);

        $client = new Client($config);

        $query = new Query('/ppp/secret/remove');
        $query->equal('.id', $secret);

        $client->query($query)->read();

       
        return redirect()
            ->route('router.pppsecret', $router->id)
            ->with('success', 'PPP Secret berhasil dihapus.');

    } catch (\Exception $e) {

        return redirect()
            ->route('router.pppsecret', $router->id)
            ->with('error', $e->getMessage());

    }
}

/*
|--------------------------------------------------------------------------
| ENABLE PPP SECRET
|--------------------------------------------------------------------------
*/

public function enableSecret($id, $secret)
{
    $router = Router::findOrFail($id);

    try {

        $client = new Client(new Config([
            'host' => $router->ip_router,
            'user' => $router->username,
            'pass' => $router->password,
            'port' => $router->api_port,
        ]));

        $query = new Query('/ppp/secret/enable');
        $query->equal('.id', $secret);

        $client->query($query)->read();

        return redirect()
            ->route('router.pppsecret', $router->id)
            ->with('success', 'PPP Secret berhasil diaktifkan.');

    } catch (\Exception $e) {

        return back()->with('error', $e->getMessage());

    }
}

/*
|--------------------------------------------------------------------------
| DISABLE PPP SECRET
|--------------------------------------------------------------------------
*/

public function disableSecret($id, $secret)
{
    $router = Router::findOrFail($id);

    try {

        $client = new Client(new Config([
            'host' => $router->ip_router,
            'user' => $router->username,
            'pass' => $router->password,
            'port' => $router->api_port,
        ]));

        $query = new Query('/ppp/secret/disable');
        $query->equal('.id', $secret);

        $client->query($query)->read();

        return redirect()
            ->route('router.pppsecret', $router->id)
            ->with('success', 'PPP Secret berhasil dinonaktifkan.');

    } catch (\Exception $e) {

        return back()->with('error', $e->getMessage());

    }
}

        /*
        |--------------------------------------------------------------------------
        | FORM TAMBAH PPP PROFILE
        |--------------------------------------------------------------------------
        */

        public function createProfile($id)
        {
            $router = Router::findOrFail($id);

            return view('router.ppp-profile-create', compact('router'));
        }

        /*
        |--------------------------------------------------------------------------
        | SIMPAN PPP PROFILE
        |--------------------------------------------------------------------------
        */

        public function storeProfile(Request $request, $id)
        {
            $router = Router::findOrFail($id);

            $request->validate([
                'name' => 'required',
            ]);

            try {

                $client = new Client(new Config([
                    'host' => $router->ip_router,
                    'user' => $router->username,
                    'pass' => $router->password,
                    'port' => $router->api_port,
                ]));

                $query = new Query('/ppp/profile/add');

                $query->equal('name', $request->name);

                if ($request->local_address) {
                    $query->equal('local-address', $request->local_address);
                }

                if ($request->remote_address) {
                    $query->equal('remote-address', $request->remote_address);
                }

                if ($request->rate_limit) {
                    $query->equal('rate-limit', $request->rate_limit);
                }

                $query->equal('only-one', $request->only_one);

                $client->query($query)->read();

                return redirect()
                    ->route('router.pppprofile', $router->id)
                    ->with('success', 'PPP Profile berhasil ditambahkan.');

            } catch (\Exception $e) {

                return back()->withInput()->with('error', $e->getMessage());

            }
        }


        /*
        |--------------------------------------------------------------------------
        | FORM EDIT PPP PROFILE
        |--------------------------------------------------------------------------
        */

        public function editProfile($id, $profile)
        {
            $router = Router::findOrFail($id);

            try {

                $client = new Client(new Config([
                    'host' => $router->ip_router,
                    'user' => $router->username,
                    'pass' => $router->password,
                    'port' => $router->api_port,
                ]));

                $query = new Query('/ppp/profile/print');
                $query->equal('.proplist',
                    '.id,name,local-address,remote-address,rate-limit,only-one');

                $profiles = $client->query($query)->read();

                $data = null;

                foreach ($profiles as $item) {

                    if (($item['.id'] ?? '') == $profile) {

                        $data = $item;
                        break;

                    }

                }

                if (!$data) {

                    return back()->with('error','PPP Profile tidak ditemukan.');

                }

                return view(
                    'router.ppp-profile-edit',
                    compact('router','data')
                );

            } catch (\Exception $e) {

                return back()->with('error',$e->getMessage());

            }
        }

    /*
    |--------------------------------------------------------------------------
    | UPDATE PPP PROFILE
    |--------------------------------------------------------------------------
    */

    public function updateProfile(Request $request, $id, $profile)
    {
        $router = Router::findOrFail($id);

        try {

            $client = new Client(new Config([
                'host' => $router->ip_router,
                'user' => $router->username,
                'pass' => $router->password,
                'port' => $router->api_port,
            ]));

            $query = new Query('/ppp/profile/set');

            $query->equal('.id', $profile);

            $query->equal('name', $request->name);

            $query->equal('local-address', $request->local_address);

            $query->equal('remote-address', $request->remote_address);

            $query->equal('rate-limit', $request->rate_limit);

            $query->equal('only-one', $request->only_one);

            $client->query($query)->read();

            return redirect()
                ->route('router.pppprofile',$router->id)
                ->with('success','PPP Profile berhasil diupdate.');

        } catch (\Exception $e) {

            return back()->with('error',$e->getMessage());

        }
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE PPP PROFILE
    |--------------------------------------------------------------------------
    */

    public function deleteProfile($id, $profile)
    {
        $router = Router::findOrFail($id);

        try {

            $client = new Client(new Config([
                'host' => $router->ip_router,
                'user' => $router->username,
                'pass' => $router->password,
                'port' => $router->api_port,
            ]));

            $query = new Query('/ppp/profile/remove');

            $query->equal('.id', $profile);

            $client->query($query)->read();

            return redirect()
                ->route('router.pppprofile',$router->id)
                ->with('success','PPP Profile berhasil dihapus.');

        } catch (\Exception $e) {

            return back()->with('error',$e->getMessage());

        }
    }

/*
|--------------------------------------------------------------------------
| HAPUS ROUTER
|--------------------------------------------------------------------------
*/
public function destroy(Router $router)
{
    $router->delete();

    return redirect()
        ->route('router.index')
        ->with('success', 'Router berhasil dihapus.');
}

}