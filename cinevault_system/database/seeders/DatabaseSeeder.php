<?php
 
namespace Database\Seeders;
 
use App\Models\User;
use App\Models\Movie;
use App\Models\Rental;
use App\Models\AuditLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
 
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Users ────────────────────────────────────────────────────
        $admin = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@cinevault.ph',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'phone'    => '09171000001',
        ]);
 
        $staff = User::create([
            'name'     => 'Staff Member',
            'email'    => 'staff@cinevault.ph',
            'password' => Hash::make('password'),
            'role'     => 'staff',
            'phone'    => '09171000002',
        ]);
 
        $user = User::create([
            'name'     => 'Regular User',
            'email'    => 'user@cinevault.ph',
            'password' => Hash::make('password'),
            'role'     => 'user',
            'phone'    => '09171000003',
        ]);
 
        // ─── Movies ───────────────────────────────────────────────────
        $movies = [
            ['title'=>'The Dark Knight',        
             'genre'=>'Action',    
             'year'=>2008,
             'director'=>'Christopher Nolan',   
             'duration'=>152,'rating'=>'PG-13',
             'price_per_day'=>75,
             'poster_icon'=>'fa-solid fa-mask',
             'description'=>'When the Joker wreaks havoc on Gotham, Batman faces his greatest test.'
            ],
            ['title'=>'Inception',              
             'genre'=>'Sci-Fi',    
             'year'=>2010,'director'=>'Christopher Nolan',   
             'duration'=>148,'rating'=>'PG-13',
             'price_per_day'=>80,
             'poster_icon'=>'fa-solid fa-brain',
             'description'=>'A thief who steals corporate secrets through dream-sharing technology.'
            ],
            ['title'=>'La La Land',             'genre'=>'Romance',   'year'=>2016,'director'=>'Damien Chazelle',     'duration'=>128,'rating'=>'PG',   'price_per_day'=>60,'poster_emoji'=>'🌟','description'=>'A pianist and an aspiring actress fall in love in Los Angeles.'],
            ['title'=>'Get Out',                'genre'=>'Horror',    'year'=>2017,'director'=>'Jordan Peele',        'duration'=>104,'rating'=>'R',    'price_per_day'=>65,'poster_emoji'=>'👁️','description'=>'A young man visits his girlfriend\'s parents and uncovers a dark secret.'],
            ['title'=>'Parasite',               'genre'=>'Drama',     'year'=>2019,'director'=>'Bong Joon-ho',        'duration'=>132,'rating'=>'R',    'price_per_day'=>70,'poster_emoji'=>'🏠','description'=>'Class discrimination threatens two families in Seoul.'],
            ['title'=>'Avengers: Endgame',      'genre'=>'Action',    'year'=>2019,'director'=>'Russo Brothers',      'duration'=>181,'rating'=>'PG-13','price_per_day'=>85,'poster_emoji'=>'⚡','description'=>'The Avengers take a final stand against Thanos.'],
            ['title'=>'Spirited Away',          'genre'=>'Animation', 'year'=>2001,'director'=>'Hayao Miyazaki',      'duration'=>125,'rating'=>'PG',   'price_per_day'=>65,'poster_emoji'=>'🐉','description'=>'A 10-year-old girl wanders into a world ruled by gods and spirits.'],
            ['title'=>'The Godfather',          'genre'=>'Drama',     'year'=>1972,'director'=>'Francis Coppola',     'duration'=>175,'rating'=>'R',    'price_per_day'=>70,'poster_emoji'=>'🌹','description'=>'The patriarch of a crime dynasty transfers control to his son.'],
            ['title'=>'Joker',                  'genre'=>'Thriller',  'year'=>2019,'director'=>'Todd Phillips',       'duration'=>122,'rating'=>'R',    'price_per_day'=>70,'poster_emoji'=>'🃏','description'=>'The origin story of the iconic DC villain.'],
            ['title'=>'Everything Everywhere All at Once','genre'=>'Sci-Fi','year'=>2022,'director'=>'Daniels',       'duration'=>139,'rating'=>'R',    'price_per_day'=>75,'poster_emoji'=>'🥟','description'=>'An immigrant discovers she alone can save the multiverse.'],
            ['title'=>'Coco',                   'genre'=>'Animation', 'year'=>2017,'director'=>'Lee Unkrich',         'duration'=>105,'rating'=>'PG',   'price_per_day'=>60,'poster_emoji'=>'💀','description'=>'A boy travels to the Land of the Dead to find his great-great-grandfather.'],
            ['title'=>'The Notebook',           'genre'=>'Romance',   'year'=>2004,'director'=>'Nick Cassavetes',     'duration'=>123,'rating'=>'PG-13','price_per_day'=>55,'poster_emoji'=>'💌','description'=>'A passionate young man falls in love with a rich young woman.'],
        ];
 
        foreach ($movies as $data) {
            Movie::create(array_merge($data, ['added_by' => $admin->id]));
        }
 
        // ─── Sample Rentals ───────────────────────────────────────────
        Rental::create([
            'movie_id'          => 2,
            'user_id'           => $user->id,
            'customer_name'     => 'Maria Santos',
            'customer_contact'  => '09171234567',
            'rental_date'       => now()->subDays(3),
            'due_date'          => now()->addDays(1),
            'days'              => 4,
            'price_per_day'     => 80,
            'total_amount'      => 320,
            'payment_method'    => 'gcash',
            'payment_reference' => 'GCX-78234',
            'status'            => 'active',
            'processed_by'      => $admin->id,
        ]);
 
        Rental::create([
            'movie_id'         => 5,
            'customer_name'    => 'Jose Cruz',
            'customer_contact' => '09281234567',
            'rental_date'      => now()->subDay(),
            'due_date'         => now()->addDay(),
            'days'             => 2,
            'price_per_day'    => 70,
            'total_amount'     => 140,
            'payment_method'   => 'cash',
            'status'           => 'active',
            'processed_by'     => $staff->id,
        ]);
 
        // Update those movies to rented
        Movie::find(2)->update(['status' => 'rented']);
        Movie::find(5)->update(['status' => 'rented']);
 
        // ─── Audit Logs ───────────────────────────────────────────────
        AuditLog::create([
            'user_id'     => $admin->id,
            'action'      => 'SEED',
            'description' => 'Database seeded with initial data.',
        ]);
    }
}