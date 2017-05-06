<?php

return
[
	/**
	 * ---------------------------------------------------------
	 * GIBBERISH DICTIONARY
	 * ---------------------------------------------------------
	 *
	 * Path to gibberish dictionary
	 */
	'gibberish_lib' => KANSO_DIR.'/Framework/Security/SPAM/Gibberish/Gibberish.txt',

	/**
	 * ---------------------------------------------------------
	 * BLACKLIST
	 * ---------------------------------------------------------
	 *
	 * Blacklisted terms results in immediate SPAM recognition.
	 *
	 * constructs  : Sentences that begin with a phrase to flag.
	 * ipaddresses : List of ip addresses to flag.
	 * urls        : URLs or URL shorteners to flag.
	 * words       : List of words to flag.
	 * html        : HTML to flag.
	 */
	'blacklist' => [

		'constructs' =>
		[
			'Nice collection',
			'you may want to add more ',
			'there is a tutorial',
			'I was looking for one which could help me',
			'These are some great resources.',
			'I found that as a developer my problem is that there are so many tools and technologies out there',
			'I think it\'ll be useful for you',
			'You have made such great points. I have to share them with my nerdy awesome twitter friends.',
			'I really appreciate when someone write a review based on real thinking',
		],

		'ipaddresses' =>
		[
		],

		'urls' =>
		[
			'2ipe.tk', '2u4.us', '4ft.me', 'an0n.me', 'ano.gd', 'ateffa.ms', 'b54.in', 'bit.ly', 'cek.li', 'clck.ru', 'cort.as', 'fc.cx', 'ge.tt', 'goo.gl', 'hfg.cc', 'is.gd', 'just.as', 'loorg.de', 'onip.it', 'ow.ly', 'past.is', 'smsh.me', 'sn.im', 'snipurl.co', 'stan.to', 'su0.ru', 't.co', 'tiny.cc', 'tinyls.net', 'tinyurl.co', 'to.ly', 'tr.im', 'urlms.co',
		],

	
		'words' =>
		[
			'dotcom','anal','anus','arse','ass','ballsack','balls','bastard','bitch','biatch','bloody','blowjob','blow job','bollock','bollok','boner','boob','bugger','bum','butt','buttplug','clitoris','cock','coon','crap','cunt','damn','dick','dildo','dyke','fag','feck','fellate','fellatio','felching','fuck','f u c k','fudgepacker','fudge packer','flange','Goddamn','God damn','hell','homo','jerk','jizz','knobend','knob end','labia','lmao','lmfao','muff','nigger','nigga','omg','penis','piss','poop','prick','pube','pussy','queer','scrotum','sex','shit','s hit','sh1t','slut','smegma','spunk','tit','tosser','turd','twat','vagina','wank','whore','wtf','4r5e','50 yard cunt punt','5h1t','5hit','a_s_s','a2m','a55','adult','amateur','anal','anal impaler','anal leakage','anilingus','anus','ar5e','arrse','arse','arsehole','ass','ass fuck','asses','assfucker','ass-fucker','assfukka','asshole','asshole','assholes','assmucus','assmunch','asswhole','autoerotic','b!tch','b00bs','b17ch','b1tch','ballbag','ballsack','bangbros','bareback','bastard','beastial','beastiality','beef curtain','bellend','bestial','bestiality','bi+ch','biatch','bimbos','birdlock','bitch','bitch tit','bitcher','bitchers','bitches','bitchin','bitching','bloody','blow job','blow me','blow mud','blowjob','blowjobs','blue waffle','blumpkin','boiolas','bollock','bollok','boner','boob','boobs','booobs','boooobs','booooobs','booooooobs','breasts','buceta','bugger','bum','bunny fucker','bust a load','busty','butt','butt fuck','butthole','buttmuch','buttplug','c0ck','c0cksucker','carpet muncher','carpetmuncher','cawk','chink','choade','chota bags','cipa','cl1t','clit','clit licker','clitoris','clits','clitty litter','clusterfuck','cnut','cock','cock pocket','cock snot','cockface','cockhead','cockmunch','cockmuncher','cocks','cocksuck ','cocksucked ','cocksucker','cock-sucker','cocksucking','cocksucks ','cocksuka','cocksukka','cok','cokmuncher','coksucka','coon','cop some wood','cornhole','corp whore','cox','cum','cum chugger','cum dumpster','cum freak','cum guzzler','cumdump','cummer','cumming','cums','cumshot','cunilingus','cunillingus','cunnilingus','cunt','cunt hair','cuntbag','cuntlick ','cuntlicker ','cuntlicking ','cunts','cuntsicle','cunt-struck','cut rope','cyalis','cyberfuc','cyberfuck ','cyberfucked ','cyberfucker','cyberfuckers','cyberfucking ','d1ck','damn','dick','dick hole','dick shy','dickhead','dildo','dildos','dink','dinks','dirsa','dirty Sanchez','dlck','dog-fucker','doggie style','doggiestyle','doggin','dogging','donkeyribber','doosh','duche','dyke','eat a dick','eat hair pie','ejaculate','ejaculated','ejaculates ','ejaculating ','ejaculatings','ejaculation','ejakulate','erotic','f u c k','f u c k e r','f_u_c_k','f4nny','facial','fag','fagging','faggitt','faggot','faggs','fagot','fagots','fags','fanny','fannyflaps','fannyfucker','fanyy','fatass','fcuk','fcuker','fcuking','feck','fecker','felching','fellate','fellatio','fingerfuck ','fingerfucked ','fingerfucker ','fingerfuckers','fingerfucking ','fingerfucks ','fist fuck','fistfuck','fistfucked ','fistfucker ','fistfuckers ','fistfucking ','fistfuckings ','fistfucks ','flange','flog the log','fook','fooker','fuck hole','fuck puppet','fuck trophy','fuck yo mama','fuck','fucka','fuck-ass','fuck-bitch','fucked','fucker','fuckers','fuckhead','fuckheads','fuckin','fucking','fuckings','fuckingshitmotherfucker','fuckme ','fuckmeat','fucks','fucktoy','fuckwhit','fuckwit','fudge packer','fudgepacker','fuk','fuker','fukker','fukkin','fuks','fukwhit','fukwit','fux','fux0r','gangbang','gangbang','gang-bang','gangbanged ','gangbangs ','gassy ass','gaylord','gaysex','goatse','god','god damn','god-dam','goddamn','goddamned','god-damned','ham flap','hardcoresex ','hell','heshe','hoar','hoare','hoer','homo','homoerotic','hore','horniest','horny','hotsex','how to kill','how to murdep','jackoff','jack-off ','jap','jerk','jerk-off ','jism','jiz ','jizm ','jizz','kawk','kinky Jesus','knob','knob end','knobead','knobed','knobend','knobend','knobhead','knobjocky','knobjokey','kock','kondum','kondums','kum','kummer','kumming','kums','kunilingus','kwif','l3i+ch','l3itch','labia','LEN','lmao','lmfao','lmfao','lust','lusting','m0f0','m0fo','m45terbate','ma5terb8','ma5terbate','mafugly','masochist','masterb8','masterbat*','masterbat3','masterbate','master-bate','masterbation','masterbations','masturbate','mof0','mofo','mo-fo','mothafuck','mothafucka','mothafuckas','mothafuckaz','mothafucked ','mothafucker','mothafuckers','mothafuckin','mothafucking ','mothafuckings','mothafucks','mother fucker','mother fucker','motherfuck','motherfucked','motherfucker','motherfuckers','motherfuckin','motherfucking','motherfuckings','motherfuckka','motherfucks','muff','muff puff','mutha','muthafecker','muthafuckker','muther','mutherfucker','n1gga','n1gger','nazi','need the dick','nigg3r','nigg4h','nigga','niggah','niggas','niggaz','nigger','niggers ','nob','nob jokey','nobhead','nobjocky','nobjokey','numbnuts','nut butter','nutsack','omg','orgasim ','orgasims ','orgasm','orgasms ','p0rn','pawn','pecker','penis','penisfucker','phonesex','phuck','phuk','phuked','phuking','phukked','phukking','phuks','phuq','pigfucker','pimpis','piss','pissed','pisser','pissers','pisses ','pissflaps','pissin ','pissing','pissoff ','poop','porn','porno','pornography','pornos','prick','pricks ','pron','pube','pusse','pussi','pussies','pussy','pussy fart','pussy palace','pussys ','queaf','queer','rectum','retard','rimjaw','rimming','s hit','s.o.b.','s_h_i_t','sadism','sadist','sandbar','sausage queen','schlong','screwing','scroat','scrote','scrotum','semen','sex','sh!+','sh!t','sh1t','shag','shagger','shaggin','shagging','shemale','shi+','shit','shit fucker','shitdick','shite','shited','shitey','shitfuck','shitfull','shithead','shiting','shitings','shits','shitted','shitter','shitters ','shitting','shittings','shitty ','skank','slope','slut','slut bucket','sluts','smegma','smut','snatch','son of a bitch','spac','spunk','t1tt1e5','t1tties','teets','teez','testical','testicle','tit','tit wank','titfuck','tits','titt','tittie5','tittiefucker','titties','tittyfuck','tittywank','titwank','tosser','turd','tw4t','twat','twathead','twatty','twunt','twunter','v14gra','v1gra','vagina','viagra','vulva','w00se','wang','wank','wanker','wanky','whoar','whore','willies','willy','wtf','xrated','xxx',
		],

		'html' =>
		[
			'href="javascript',
			'href=\'javascript',
			'<script>',
			'(javascript:)',
			'javascript:',
		],
	],

	/**
	 * ---------------------------------------------------------
	 * GRAYLIST
	 * ---------------------------------------------------------
	 *
	 * Graylisted terms results in bad score by the SPAM protector.
	 *
	 * constructs  : Sentences that begin with a phrase to flag.
	 * urls        : URLs or URL shorteners to flag.
	 * words       : List of words to flag.
	 * html        : HTML to flag.
	 */
	'graylist' =>
	[
		'constructs' =>
		[
			'interesting', 'sorry', 'nice', 'great', 'awesome', 'amazing', 'cool', 'beautiful', 'impressive', 'stunning', 'wonderful', 'striking', 'fascinating', 'incredible', 'marvelous', 'surprising', 'unbelievable', 'astonishing',
		],

		'urls' =>
		[
			'.cn', '.de', '.pl', '.xxx', '.xn', '.biz', '.ru', '.info', '.asia', '.in', '.xyz', '.at', '.click ', '.club', '.country', '.eu', '.in', '.link', '.me', '.mobi', '.rocks', '.website', '.best', '.shop', '.work', '.ninja', '.science', '.space',
		],

		'words' =>
		[
			'free', '!!!', '#1', '$$$', '100% free', '100% satisfied', '4u', '50% off', 'accept credit cards', 'acceptance', 'access', 'accordingly', 'act now!', 'ad', 'additional income', 'addresses on cd', 'affordable', 'all capitals', 'all natural', 'all new', 'amazing', 'apply now', 'apply online', 'as seen on', 'attention', 'auto email removal', 'avoid', 'avoid bankruptcy', 'bad credit', 'bargain', 'be your own boss', 'being a member', 'beneficiary', 'best price', 'beverage', 'big bucks', 'billing address', 'billion', 'billion dollars', 'bonus', 'brand new pager', 'bulk email', 'buy', 'buy direct', 'buying judgments', 'cable converter', 'call', 'call free', 'call now', 'calling creditors', 'can\'t live without', 'cancel at any time', 'cancel at anytime', 'cannot be combined with any other offer', 'can’t live without', 'cards accepted', 'cash', 'cash bonus', 'cashcashcash', 'casino', 'celebrity', 'cents on the dollar', 'certified', 'chance', 'cheap', 'check', 'check or money order', 'claims', 'clearance', 'click', 'click / click here / click below', 'click below', 'click here', 'click to remove', 'collect', 'collect child support', 'compare', 'compare rates', 'compete for your business', 'confidentially on all orders', 'congratulations', 'consolidate debt and credit', 'consolidate your debt', 'copy accurately', 'copy dvds', 'cost', 'cost / no cost', 'costs', 'credit', 'credit bureaus', 'credit card offers', 'credit cards', 'cures baldness', 'deal', 'dear [email/friend/somebody]', 'dear friend', 'decision', 'diagnostics', 'dig up dirt on friends', 'direct email', 'direct marketing', 'discount', 'do it today', 'don\'t delete', 'don\'t hesitate', 'don’t delete', 'don’t hesitate', 'don’t hesitate!', 'dormant', 'double your', 'double your income', 'drastically reduced', 'e.x.t.r.a. punctuation', 'earn', 'earn $', 'earn extra cash', 'earn per week', 'easy terms', 'eliminate bad credit', 'eliminate debt', 'email harvest', 'email marketing', 'expect to earn', 'explode your business', 'extra income', 'f r e e', 'fantastic deal', 'fast cash', 'fast viagra delivery', 'fees', 'financial freedom', 'financially independent', 'for free', 'for instant access', 'for just $xxx', 'for only', 'for you', 'form', 'free', 'free access', 'free and free', 'free cell phone', 'free consultation', 'free dvd', 'free gift', 'free grant money', 'free hosting', 'free installation', 'free instant', 'free investment', 'free leads', 'free membership', 'free money', 'free offer', 'free preview', 'free priority mail', 'free quote', 'free sample', 'free trial', 'free website', 'freedom', 'friend', 'full refund', 'get', 'get it now', 'get out of debt', 'get paid', 'get started now', 'gift certificate', 'give it away', 'giving away', 'great offer', 'guarantee', 'guaranteed', 'have you been turned down?', 'hello', 'here', 'hidden', 'hidden assets', 'hidden charges', 'home', 'home based', 'home employment', 'homebased business', 'hot', 'human growth hormone', 'if only it were that easy', 'important information regarding', 'in accordance with laws', 'income', 'income from home', 'increase', 'increase sales', 'increase traffic', 'increase your sales', 'incredible deal', 'info you requested', 'information you requested', 'instant', 'insurance', 'internet market', 'internet marketing', 'investment', 'investment', 'no investment', 'investment decision', 'it’s effective', 'join millions', 'join millions of americans', 'laser printer', 'leave', 'legal', 'life', 'life insurance', 'lifetime', 'limited time', 'limited time offer', 'loans', 'long distance phone offer', 'lose', 'lose weight', 'lose weight spam', 'lower interest rate', 'lower monthly payment', 'lower your mortgage rate', 'lowest insurance rates', 'lowest price', 'luxury car', 'mail in order form', 'maintained', 'make $', 'make money', 'make money fast', 'marketing', 'marketing solutions', 'mass email', 'medicine', 'medium', 'meet singles', 'member', 'message contains', 'million', 'million dollars', 'miracle', 'money', 'money back', 'money making', 'month trial offer', 'more internet traffic', 'mortgage', 'mortgage rates', 'multi level marketing', 'name brand', 'never', 'new customers only', 'new domain extensions', 'nigerian', 'no age restrictions', 'no catch', 'no claim forms', 'no cost', 'no credit check', 'no disappointment', 'no experience', 'no fees', 'no gimmick', 'no gimmicks', 'no hidden', 'no hidden costs', 'no inventory', 'no investment', 'no medical exams', 'no middleman', 'no obligation', 'no purchase necessary', 'no questions asked', 'no selling', 'no strings attached', 'no-obligation', 'not intended', 'notspam', 'now', 'now only', 'obligation', 'off shore', 'offer', 'offer expires', 'once in lifetime', 'one hundred percent free', 'one hundred percent guaranteed', 'one time', 'one time / one-time', 'one time mailing', 'online biz opportunity', 'online degree', 'online marketing', 'online pharmacy', 'only', 'only $', 'open', 'opportunity', 'opt in', 'order', 'order', 'order now', 'order today', 'order status', 'order now', 'order status', 'order today', 'orders shipped by', 'orders shipped by priority mail', 'outstanding values', 'partners', 'passwords', 'pennies a day', 'per day', 'per week', 'performance', 'phone', 'please read', 'potential earnings', 'pre-approved', 'price', 'print form signature', 'print out and fax', 'priority mail', 'prize', 'prizes', 'problem', 'produced and sent out', 'profits', 'promise you', 'pure profit', 'quote', 'rates', 'real thing', 'refinance', 'refinance home', 'removal instructions', 'remove', 'removes wrinkles', 'requires initial investment', 'reserves the right', 'reverses', 'reverses aging', 'risk free', 'rolex', 'sale', 'sales', 'sample', 'satisfaction', 'satisfaction guaranteed', 'save $', 'save big money', 'save up to', 'score with babes', 'search engine listings', 'search engines', 'see for yourself', 'selling', 'sent in compliance', 'serious cash', 'shopper', 'shopping spree', 'sign up free today', 'social security number', 'solution', 'special promotion', 'stainless steel', 'stock alert', 'stock disclaimer statement', 'stock pick', 'stop', 'stop snoring', 'stuff on sale', 'subject to credit', 'subscribe', 'success', 'supplies are limited', 't e x t w i t h g a p s', 'take action now', 'teen', 'terms and conditions', 'the best rates', 'the following form', 'they keep your money -- no refund!', 'they keep your money — no refund!', 'they’re just giving it away', 'this isn\'t junk', 'this isn\'t spam', 'this isn’t junk', 'this isn’t spam', 'thousands', 'time limited', 'trial', 'undisclosed recipient', 'university diplomas', 'unlimited', 'unsecured credit', 'unsecured debt', 'unsolicited', 'unsubscribe', 'urgent', 'us dollars', 'vacation', 'vacation offers', 'valium', 'viagra', 'vicodin', 'visit our website', 'warranty', 'we hate spam', 'we honor all', 'web traffic', 'weekend getaway', 'weight loss', 'what are you waiting for', 'while supplies last', 'while you sleep', 'who really wins?', 'why pay more?', 'wife', 'will not believe your eyes', 'win', 'winner', 'winning', 'won', 'work at home', 'work from home', 'xanax', 'you are a winner', 'you have been selected', 'your income', 'you’re a winner', 'I\'ve always been curious', 'I\'d love to hear more about',
		],

		'html' =>
		[
		],
	],

	/**
	 * ---------------------------------------------------------
	 * WHITELIST
	 * ---------------------------------------------------------
	 *
	 * Whitelisted terms will always result in a PASS.
	 *
	 * constructs  : Sentences that begin with a phrase to flag.
	 * urls        : URLs or URL shorteners to flag.
	 * words       : List of words to flag.
	 * html        : HTML to flag.
	 */
	'whitelist' =>
	[
		
		'constructs' =>
		[
		],

		'ipaddresses' =>
		[
		],

		'urls' =>
		[
		],

		'words' =>
		[
		],

		'html' =>
		[
		],
	],

];
