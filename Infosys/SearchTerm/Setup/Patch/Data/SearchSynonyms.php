<?php
/**
 * @package   Infosys/SearchTerm
 * @version   1.0.0
 * @author    Infosys Limited
 * @copyright Copyright © 2021. All Rights Reserved.
 */
namespace Infosys\SearchTerm\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Search\Model\SynonymGroupRepository;
use Magento\Search\Api\Data\SynonymGroupInterface;

/**
 * Class to create synonyms
 */
class SearchSynonyms implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var SynonymGroupInterface
     */
    private $synonymGroup;
    /**
     * @var SynonymGroupRepository
     */
    private $synonymGroupRepository;
    
    /**
     * Constructor function
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param SynonymGroupInterface $synonymGroupInterface
     * @param SynonymGroupRepository $synonymGroupRepository
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        SynonymGroupInterface $synonymGroupInterface,
        SynonymGroupRepository $synonymGroupRepository
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->synonymGroup = $synonymGroupInterface;
        $this->synonymGroupRepository = $synonymGroupRepository;
    }
    /**
     * Patch to create synonymGroup
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $arr =[
                    'Filter, filters, cabin air filter, air filter, engine air filter, oil filter, fuel filter, 
					fliter, filtter, CAF, feul, fylter, element, Filter, fliter, fillter, filtter, fylter, element,
					Oil filter, fliter, fillter, filtter, fylterelement, ol filter, oile filter, air filter, 
					fliter, fillter, filtter, fylterelement, are filter, ayre filter,Cabin filter, fliter, fillter ,
					filtter, fylter, caben, caban, cabbin, CAF, element, cabin air filter, fliter, fillter, filtter, 
					fylter, caben, caban, cabbin, CAF, element, engine air filter, fliter, fillter, filtter, fylter, 
					injen, engin, motor, element, enjun filter',
        
                    'battery, batteries, batery, bateries, car battery, Lexus battery, CCA battery,
					hybrid battery, hybred battery',
        
                    'Mirror, miror, mirrer, mirrar, rearview mirror, driver mirror, Mirror, miror, mirrer, mirrar, 
					 rearview mirror, driver mirror, Mirror, miror, mirrer, mirrar, rearview mirror, driver mirror, 
					 Mirror, miror, mirrer, mirrar, rearview mirror, driver mirror, Mirror, miror, mirrer, mirrar, 
					 rearview mirror, driver mirror, Mirror, miror, mirrer, mirrar, rearview mirror, driver mirror, 
					 Rear bumper, bumper, bumpper, bumper cover, back bumper',
        
                    'Roof rack, rack, travel rack, ski rack, SUV rack, rac, rak',
    
                    'floor mats, floor mat, driver side mat, passenger side mat, carpet mat, carpet floor mat,
                    rubber mat, rubber floor mat, all weather mat, car mats, floor mats, driver side mat,
                    passenger side mat, carpet mat, carpet floor mat, rubber mat, rubber floor mat, all weather mat',
        
                    'spark plug, spark plugs, copper spark plug, titanium spark plug, irridium spark plug',
        
                    'Rim, Rims, wheel, wheels, chrome wheel, chrome rim, alloy wheel, alloy rim, 5 spoke wheel, 
					five spoke wheel, 20 inch wheel, 19 inch wheel, black out wheel, 4runner wheel, tacoma wheel, 
					Wheels, wheel, wheel lock, alloy, aluminun, Wheel lock, McGard, security, lugnut, Lugbolts, 
					nut, stud',
        
                    'Alternator, alternater, generator, generater, voltage, voltige, voltedge',
        
                    'Brake pads, brake pad, brake rotor, disc, lining, semi, metallic, shoe, parking, Pad, pads,
					lining, semi, metallic, shoe, parking,Brake, break, braks, brakes, Rotor, rotors, Rotr, rtr, 
					disc, roter, rowter, rowtur, Brake rotor',
        
                    'Coolant, fluid, water, flooid, flewid, glycol',
    
                    'Radiator, radiater',
        
                    'wiper blade, wiper blades, wiper, wipers, insert, whyper',
        
                    'catalytic converter, catalytic, cadillac, catalyst, exhaust, manifold, muffler, resonate, 
					resonator, resonater, Pipe',
        
                    'transmission, tranny, case, transmishun, shift, clutch',
        
                    'key FOB, remote, wireless, lock, locks, wireless control, control, frequency operated button',
    
                    'Skid plate, under cover, cover, steel',
        
                    'Gas cap, fuel, filler',
        
                    'Paint, touch up, touchup',
        
                    'Transmission fluid, oil, power steering',
        
                    'navigation, display, Nav, audio, telematics, infotainment, radio, Navigation update, card, 
					SD Card, Hard Drive, Disc',
    
                    'Windshield, Wind Shield, Windsheild, Wendshield, Wendsheild, Whenshield, Whensheild, 
					Iwndshield, Iwndsheild',
        
                    'Water pump, waterpump, Awterpump, whater pump, whaterpump',
        
                    'Fender, efnder, Finder, fin der, Fender liner, Fender liner, Fenderliner, fender ilner,
					finder ilner, fin der liner, finder line',
        
                    'Hood, ohod, hod, bonnet, obnnet, bonet, car hood, truck hood, truck hod, suv hood',
        
                    'Tires, Itres, tyers, tire, tyer, tiers, tier',
        
                    'Grille, grill, rgill, grell, gelle, gell, grull, grulle',
        
                    'touch up paint, touchup paint, touch-up paint, otuch-up paint, toch-up paint, tochup paint, 
					tuchup paint, tuch-up paint, touch-up pant, touch-up pint, touchup pant, touchup pint',
        
                    'door, odor, dor, dore, doors',
        
                    'Trd, rtd, Toyota Racing Development, terd',
        
                    'running boards, board, run board, truck board, runner boards, run boards, runs board, 
					obards, Runing boards, Runing bords, Runing bards, urnning boards',
        
                    'Antenna, antena, antinna, antina, antea, antenn, entenna',
        
                    'Sun visor, visor, sunvisor, usn visor, sun-visor, sun viser, son visor, son viser, 
					sun veser, sun vesor, sun vesor, isvor, evsor, viser, veser, ivser, evser, sun-viser, 
					son-visor, son-viser, sun-veser, sun-vesor',
        
                    'CV Axle, cv axel, VC Axle, VC Axel, axel, ax el, ax le, axele, axal, axale',
        
                    'Radio, ardio, raydeo, radeo, rodeo, roadieo, redeo',
        
                    'atf, taf, automatic transmission fluid, transmission fluid, autmatic fluid, transmisson oil, 
					automatic transmission oil, automatic tranmission fluid',
        
                    'Exhaust, exhausted, xehaust, exhast, exhust, xhaust, exhawst, exaust, exust',
        
                    'thermostat, htermostat, thermstat, thirmstat, therastat, termostat, termostate, htermostate,
					thermo-state, thermstate, thirmostat, therastate',
        
                    'cover, ocver, caver, civer, covr, cavr, civr, Seat cover, Seat covers, seat-cover, esat-cover, 
					set-cover, set cover, sete cover, seat covr, set covr, sete covr, seat caver, set caver, sete caver,
					Car cover, care cover, car cuver, car cover waterproof, Cargo cover, tonneau cover, Cargo, cargo mat,
					cargo cover, cargo net, Cargo net, cargo net for pickup, cargo net for truck, cargo net for suv,
					Cargo mat, cargo mats, cargo mat for suv, cargo mats for truck, cargo mats rubber, cargo mat liner',
     
                    'Fuel pump, fule pump, fuel punp, fule punp, guel pump, fuel pumps, fiel pump',
        
                    'tailgate, talegate, tellgate, tailgete, tailget, liftgate, tail gate, tailgates',
        
                    'Starter, stater, startar, starters',
        
                    'Ignition coil, ignition cole, ignishun coil, igniton coil, igniton cole, ignition coal, 
					ignition coils, ignitions coil',
        
                    'axle, axel, axl, axles',
        
                    'seat, seet, sete, seats',
        
                    'hitch, tow hitch, toe hitch, towe hitch',
        
                    'Trd wheels, trd wheel, matte black trd wheel, matte black wheel, bronze wheel, gray wheel, 
					matt black wheel, trd weels, trd weel',
        
                    'wheel bearing, wheels baring, wheel bearings, weel bearing, wheel baring, wheel bering, 
					weel baring, weel bering, wheel vearing',
        
                    'emblem, emblam, emblems',
                
                    'TPMS, tire pressure monitoring system, tmps, tp monitoring system, tire presure monitering system',
        
                    'door handle, door handles, doar handle, handle, door handel, door handels',
        
                    'strut, struts, stut, stuts, shock, shocks',
        
                    'fuel cap, fiel cap, fule cap, fuel caps',
        
                    'Window, windows, windoe, glass window, door window',
        
                    'remote start, remot start, remote, stat, remote state, remot stat',
        
                    'bed mat, truck bed mat, truck mat, truck bed liner, truck bed matt, bed matt, bead met, bedd mat',
        
                    'belt, seat belt, timing belt, bang, bash, bat, beat, biff, blow, bop, box, buffet, bust, chop,
                    clap, clip, clout, crack, cuff, dab, douse, fillip, hack, haymaker, hit, hook, knock, larrup,
                    lash, lick, pelt, pick, plump, poke, pound, punch, rap, slam, slap, slug, smack, smash, sock,
                    spank, stinger, stripe, stroke, swat, swipe, switch, thud, thump, thwack, wallop, welt, whack,
                    wham, whop, whap, blt, bt, eat, blet, elt, set, sat, time,timming belt, sear belt, bert, sert,
                    deat, aeet, bekt, bejt, cceate, sttae, scedual, sellt, senat, steet, zertec, scear, selet,
                    seet, sagat, sefty, suate, seiz, soewhat, sebd, sceak, seanate, sevety, snator, useda, sette,
                    seista, deseart, seneate,
					esatte, seayt, seaat, seatt, se at, sea t, Timing belt, age, day, epoch, era, period, cycle, 
					generation, year, bit, space, span, spell, stretch, while, date, vintage, running belt, 
					rubber belt, engine belt, time,timming belt, blt, bt, eat, blet, elt, set, sat, time,timming belt, 
					sear belt, bert, sert, deat, aeet, bekt, bejt, bellied, bell, beat, bet, beet, felt, bolt, blt, 
					pelt, belote, best, bent, melt, oming, timithy, timeling, timming, timline, timiog, ti ming, tinning,
					seat belt, seat belts, saet, sear belt, sea belt, seat bekt, seat, be;t, sea belt, seet belt, sea belts, 
					cceate, seayt, seaat, seatt, se at, sea t, bellied, bell, beat, bet, beet, felt, bolt, blt, pelt, 
					belote, best, bent, melt',
        
                    'PTR2035110BK, PTR2035110BK, PTR2035110B, TR2035110BK',
    
                    'sensor, oxygen sensor, carbon, air detector, alarm,trigger, oxygen detector, knock sensor, carbon, 
					air detector, alarm,trigger, oxygen detector, xygen sensor, senor,ozygne, oxsgin, sensory, sensorial, 
					oxygent, axxion, nextgen, exange, oxogen, osygen, ocygen, oxyen, oxigen, exygen, exhcnage, oxyegen, 
					oxgyen, oxyigen, oxygin, oxyggen, oxygens, oxagan, oxygyn, oxeygen, oxgen, ogygen, oxegion, qxygen, 
					axigion, oygen, oxkey, exchagne, oyxgen, oxagen, knock sensor, air detector, alarm, trigger',
    
                    'Sway bar, careen, lurch, pitch, rock, roll, seesaw, toss, rod, arbor, beam, board, crossbar,
					crossbeam, band, strip, sawy, say, wasy, swat, swau, swar, swaybar, ways swat bar',
        
                    'ball joint, connection, coupling, join, joining, jointure, junction, juncture, balls, 
					joints, boll, pol, join, point, Johnetta, jaunt, count, cont., coin, giant, joined, joins',
        
                    'compressor, cmprssor, campressor, compresser, compressar, cimpressor, compression, 
					compresser, compress, press',
        
                    'roof, ceiling, top, cover, awning, canopy, ceiling, tent, roofs, rof, rufe, ruuf, rooft, rofessor',
        
                    'Spoiler, wing, tail, spoler, spooler, sooiler, suplier, soiler, spoliers, front wing,
                    back wing, lip',
        
                    'steering wheel, steer wheel, sterring weel, steering weel, seering weel, strign, weelk',
        
                    'car mats, floor mats, foor mats, car matt, floore matt, doormat, welcome mat, mats, 
					rugs, rug, carpet mats, carpets, mats, doormat',
        
                    'Key, car keys, truck key, truck keys, car keys, cars keys, door key, door keys, keya, 
					kess, kees, kee, kii, joeys, keysafe, keaps, kets,  keesp, kaoss, kyes, geys, kless',
        
                    'Sun shade, sunshade, sol, shades, sunshades, shadow, darkness, sun protection, protection, 
					sun shelter, screen, Shadd, shite, shod, shuteye, sheathe, sheath, shadow',
        
                    'Roof Rack Cross Bars, cross bars, luggage holder, ceiling rack, car rack, overhead holder, 
					cargo holder, roof rails, cargo rack, cargo carriers, cross, bars, rack, ruf rack, rof rack, 
					roof rak, roof rac, ceiling rack, corss bars, crossbar, rials, roof rials',
        
                    'Trunk, tailgate, back of car, tail, gate, car tail, turnk, tial, tialgate',
        
                    'Key glove, fob cover, key cover, cover, key holder, key chains, key protector, fob protector, 
					kye, kye holdr, holdr, protecter, key protecter, fob protecter, covor, covar, fbo',
        
                    'shocks, shock, shocks, car shock, car shocks, shock absorber, suspension springs,
                    suspension system, cushion, leaf springs, springs, absorbers, shoc, shok, absorbor, absorbar, 
					sprints, leag sprints, leaf springs, leafs sprints, shocs, shoks, skock, socke, shoch, shocke',
        
                    'mat, floor mat, car rug, floor cover, floor covering, rug, car matt, matt, flor covor, covar',
        
                    'license plate frame, license plate, Plate frame, license frame, frame, number plate frame, 
					car plate, plat frame, plat, licens, fram, pkate, palte, licence, licence plate',
        
                    'All-Weather, durable, floor liners, tray, cargo tray, cargo mat, floor mats, all weather, 
					floor lingers, all whether, whether, waether, al-weather, al-whether, all-whether',
        
                    'carpet, floor, carpeting, corpet, carpte, carpark, carprt, carpel',
        
                    'Light, tail light, head light, fog light, lights, tail lights, head lights, fog lights,
                    headlights, headlight, lighting, lamp, Fog light, high beams, brights, fog lamps, front lights,
                    rear lamp, rear lights, lite, lite, lighs, lighte, lit, gof, hed, heed, hedlight, lihgt,
                    taillight, higlight, haed, 
					haedlight, hardlight, headlike, headline, fornt lights, raer, reer, fog light, fog lights, head lights,
					head light, head lamp, headlamps, headlamp, head lamps, tail light, taillight, tail lights',
        
                    'Control arm, a-arm, controls arms, arm, upper control arm, lower control arm, upper control, 
					lower control, control, controls, amr, cuntrol, controll, conrol, contriol,
					contral, contrall, controle, cntrl',
        
                    'Dash, dashboard, control panel, indicator panel, instrument board, fascia, central console, 
					control desk, console, dashboards, panel, dashbord, dashbored, dhas, dach, bored, bord,
					panal, consol, consoll, dashbaord, dahsboard',
        
                    'Power steering, electric power steering, automotive steering, power assisted steering,
                    steering mechanism, steering system, seering, stearing, sttering, steeting, 
                    poer, powr, powersteering, powor',
        
                    'abs, anti lock braking system, anti-lock braking system, braking system, vehicle braking system, 
					anti lock, anti-lock, lock system, locking system, anti lock breaking system, anti-lock breaking system, 
					breaking system, abz, antee lock, auntie lock, aunty lock, loc, lok, sistem',
        ];
        $store_id = 0;
        $website_id = 0;
        $group_id = 1;
        foreach ($arr as $data) {
                 $words = explode(',', $data);
            $words = array_map('trim', $words);
            $data = strtolower(implode(',', $words));
            $this->synonymGroup->setGroupId($group_id);
            $this->synonymGroup->setStoreId($store_id);
            $this->synonymGroup->setWebsiteId($website_id);
            $this->synonymGroup->setSynonymGroup($data);
            $this->synonymGroupRepository->save($this->synonymGroup);
            $group_id+=1;
        }
        $this->moduleDataSetup->endSetup();
    }
    /**
     * Aliases function
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }
    /**
     * Dependencies function
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }
}
