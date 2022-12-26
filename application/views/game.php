<style>
	@media (min-width: 320px) and (max-width: 1024px) {
		.mobile-tran {
			height: 100%;
			margin-top: 0px !important;
		}

		.mobile-text {
			font-size: 15px !important;
		}
	}

	.mobile-tran {
		margin-top: 50px;
	}



	.banner-overlay {
		overflow: hidden;
	}
	#overlay {
		position: absolute;
		display: none;
		width: 100%;
		height: 100%;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background-color: #000000d1;
		z-index: 2;
		cursor: pointer;
	}

	#text{
		position: absolute;
		color: white;
		text-align: center;
		width: 100%;
		height: 100%;
		top: 25%;
	}
	figure {
		width: 100%;
		height: auto;
		margin: 0;
		padding: 0;
		background: #fff;
		overflow: hidden;
	}

	figure:hover+span {
		bottom: -36px;
		opacity: 1;
	}

	.play figure {
		position: relative;
	}

	.play figure::before {
		position: absolute;
		top: 0;
		left: -75%;
		z-index: 2;
		display: block;
		content: '';
		width: 50%;
		height: 100%;
		background: -webkit-linear-gradient(left, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, .3) 100%);
		background: linear-gradient(to right, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, .3) 100%);
		-webkit-transform: skewX(-25deg);
		transform: skewX(-25deg);
	}

	.play figure:hover::before {
		-webkit-animation: shine .75s;
		animation: shine .75s;
	}

	@-webkit-keyframes shine {
		100% {
			left: 125%;
		}
	}

	@keyframes shine {
		100% {
			left: 125%;
		}
	}

	@media (min-width: 1025px) {
		.games-nav {
			width: 69% !important;
			margin: 0 auto !important;
			margin-bottom: 20px !important;
		}

	}

	.games-main {
		background: url(img/pt1.png);

		background-color: #000;
		border-radius: 12px;
	}

	.games-nav {
		width: 100%;

	}

	.games-nav-list {
		width: 25%;
		float: left;
	}

	.games-nav-list-first img {
		border-top-left-radius: 12px;
		border-bottom-left-radius: 12px;
	}
</style>
<link rel="stylesheet" href="https://member.aba444.com/assets/css/custom.css?=2022-12-19" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.2.1/axios.min.js" integrity="sha512-zJYu9ICC+mWF3+dJ4QC34N9RA0OVS1XtPbnf6oXlvGrLGNB8egsEzu/5wgG90I61hOOKvcywoLzwNmPqGAdATA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>

	Array.prototype.หยิบ = function(k) {
		const copyOfThisArray = [...this];
		let samples = [];
		while (samples.length < k && copyOfThisArray.length > 0) {
			const r = Math.floor(Math.random() * copyOfThisArray.length);
			samples = [...samples, ...copyOfThisArray.splice(r,1)];
		}
		return samples;
	}

	const game_list = {"status":0,"data":{"seq":1,"productName":"PG Soft","productCode":"pgslot","isActive":true,"lists":[{"productCode":"pgslot","seq":1,"gameId":"1340277","gameName":"Asgardian Rising","imgUrl":"https://status-res.askmebet.com/pgslot/1340277.webp","gameType":"SLOT","order":1,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"1372643","gameName":"Diner Delights","imgUrl":"https://status-res.askmebet.com/pgslot/1372643.webp","gameType":"SLOT","order":2,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"1368367","gameName":"Alchemy Gold","imgUrl":"https://status-res.askmebet.com/pgslot/1368367.webp","gameType":"SLOT","order":3,"maintaine":false,"recommended":false,"newGame":true,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"1338274","gameName":"Totem Wonders","imgUrl":"https://status-res.askmebet.com/pgslot/1338274.webp","gameType":"SLOT","order":4,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"1312883","gameName":"Prosperity Fortune Tree","imgUrl":"https://status-res.askmebet.com/pgslot/1312883.webp","gameType":"SLOT","order":5,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"135","gameName":"Wild Bounty Showdown","imgUrl":"https://status-res.askmebet.com/pgslot/135.webp","gameType":"SLOT","order":6,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"132","gameName":"Wild Coaster","imgUrl":"https://status-res.askmebet.com/pgslot/132.webp","gameType":"SLOT","order":7,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"128","gameName":"Legend of Perseus","imgUrl":"https://status-res.askmebet.com/pgslot/128.webp","gameType":"SLOT","order":8,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"127","gameName":"Speed Winner","imgUrl":"https://status-res.askmebet.com/pgslot/127.webp","gameType":"SLOT","order":9,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"130","gameName":"Lucky Piggy","imgUrl":"https://status-res.askmebet.com/pgslot/130.webp","gameType":"SLOT","order":10,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"120","gameName":"The Queen’s Banquet","imgUrl":"https://status-res.askmebet.com/pgslot/120.webp","gameType":"SLOT","order":11,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"87","gameName":"Treasures of Aztec","imgUrl":"https://status-res.askmebet.com/pg_slot/treasures-aztec.webp","gameType":"SLOT","order":12,"maintaine":false,"recommended":true,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"89","gameName":"Lucky Neko","imgUrl":"https://status-res.askmebet.com/pg_slot/lucky-neko.webp","gameType":"SLOT","order":13,"maintaine":false,"recommended":true,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"74","gameName":"Mahjong Ways 2","imgUrl":"https://status-res.askmebet.com/pgslot/mahjong-ways2.webp","gameType":"SLOT","order":14,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"71","gameName":"Caishen Wins","imgUrl":"https://status-res.askmebet.com/pg_slot/cai-shen-wins.webp","gameType":"SLOT","order":15,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"98","gameName":"Fortune Ox","imgUrl":"https://status-res.askmebet.com/pg_slot/fortune-ox.webp","gameType":"SLOT","order":16,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"65","gameName":"Mahjong Ways","imgUrl":"https://status-res.askmebet.com/pg_slot/mahjong-ways.webp","gameType":"SLOT","order":17,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"106","gameName":"Ways of the Qilin","imgUrl":"https://status-res.askmebet.com/pg_slot/ways-of-qilin.webp","gameType":"SLOT","order":18,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"126","gameName":"Fortune Tiger","imgUrl":"https://status-res.askmebet.com/pgslot/fortune-tiger.webp","gameType":"SLOT","order":19,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"75","gameName":"Ganesha Fortune","imgUrl":"https://status-res.askmebet.com/pg_slot/ganesha-fortune.webp","gameType":"SLOT","order":20,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"117","gameName":"Cocktail Nights","imgUrl":"https://status-res.askmebet.com/pg_slot/cocktail-nite.webp","gameType":"SLOT","order":21,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"104","gameName":"Wild Bandito","imgUrl":"https://status-res.askmebet.com/pg_slot/wild-bandito.webp","gameType":"SLOT","order":22,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"110","gameName":"Jurassic Kingdom","imgUrl":"https://status-res.askmebet.com/pg_slot/jurassic-kdm.webp","gameType":"SLOT","order":23,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"125","gameName":"Butterfly Blossom","imgUrl":"https://status-res.askmebet.com/pgslot/125.webp","gameType":"SLOT","order":24,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"92","gameName":"Thai River Wonders","imgUrl":"https://status-res.askmebet.com/pg_slot/thai-river.webp","gameType":"SLOT","order":25,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"73","gameName":"Egypt's Book of Mystery","imgUrl":"https://status-res.askmebet.com/pg_slot/egypts-book-mystery.webp","gameType":"SLOT","order":26,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"68","gameName":"Fortune Mouse","imgUrl":"https://status-res.askmebet.com/pg_slot/fortune-mouse.webp","gameType":"SLOT","order":27,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"84","gameName":"Queen of Bounty","imgUrl":"https://status-res.askmebet.com/pg_slot/queen-bounty.webp","gameType":"SLOT","order":28,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"112","gameName":"Oriental Prosperity","imgUrl":"https://status-res.askmebet.com/pg_slot/oriental-pros.webp","gameType":"SLOT","order":29,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"121","gameName":"Destiny of Sun & Moon","imgUrl":"https://status-res.askmebet.com/pg_slot/121.webp","gameType":"SLOT","order":30,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"57","gameName":"Dragon Hatch","imgUrl":"https://status-res.askmebet.com/pg_slot/dragon-hatch.webp","gameType":"SLOT","order":31,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"122","gameName":"Garuda Gems","imgUrl":"https://status-res.askmebet.com/pg_slot/garuda-gems.webp","gameType":"SLOT","order":32,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"79","gameName":"Dreams of Macau","imgUrl":"https://status-res.askmebet.com/pg_slot/dreams-of-macau.webp","gameType":"SLOT","order":33,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"119","gameName":"Spirited Wonders","imgUrl":"https://status-res.askmebet.com/pg_slot/spirit-wonder.webp","gameType":"SLOT","order":34,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"60","gameName":"Leprechaun Riches","imgUrl":"https://status-res.askmebet.com/pg_slot/leprechaun-riches.webp","gameType":"SLOT","order":35,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"54","gameName":"Captain's Bounty","imgUrl":"https://status-res.askmebet.com/pgslot/captains-bounty.webp","gameType":"SLOT","order":36,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"48","gameName":"Double Fortune","imgUrl":"https://status-res.askmebet.com/pg_slot/double-fortune.webp","gameType":"SLOT","order":37,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"90","gameName":"Secret of Cleopatra","imgUrl":"https://status-res.askmebet.com/pg_slot/sct-cleopatra.webp","gameType":"SLOT","order":38,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"115","gameName":"Supermarket Spree","imgUrl":"https://status-res.askmebet.com/pg_slot/sprmkt-spree.webp","gameType":"SLOT","order":39,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"95","gameName":"Majestic Treasures","imgUrl":"https://status-res.askmebet.com/pg_slot/majestic-ts.webp","gameType":"SLOT","order":40,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"53","gameName":"The Great Icescape","imgUrl":"https://status-res.askmebet.com/pg_slot/the-great-icescape.webp","gameType":"SLOT","order":41,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"123","gameName":"Rooster Rumble","imgUrl":"https://status-res.askmebet.com/pgslot/123.webp","gameType":"SLOT","order":42,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"113","gameName":"Raider Jane's Crypt of Fortune","imgUrl":"https://status-res.askmebet.com/pg_slot/crypt-fortune.webp","gameType":"SLOT","order":43,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"118","gameName":"Mask Carnival","imgUrl":"https://status-res.askmebet.com/pg_slot/mask-carnival.webp","gameType":"SLOT","order":45,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"111","gameName":"Groundhog Harvest","imgUrl":"https://status-res.askmebet.com/pg_slot/groundhog.webp","gameType":"SLOT","order":46,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"108","gameName":"Buffalo Win","imgUrl":"https://status-res.askmebet.com/pg_slot/buffalo-win.webp","gameType":"SLOT","order":47,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"107","gameName":"Legendary Monkey King","imgUrl":"https://status-res.askmebet.com/pg_slot/lgd-monkey-kg.webp","gameType":"SLOT","order":48,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"116","gameName":"Farm Invaders","imgUrl":"https://status-res.askmebet.com/pg_slot/farm-invaders.webp","gameType":"SLOT","order":49,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"114","gameName":"Emoji Riches","imgUrl":"https://status-res.askmebet.com/pg_slot/emoji-riches.webp","gameType":"SLOT","order":50,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"82","gameName":"Phoenix Rises","imgUrl":"https://status-res.askmebet.com/pg_slot/phoenix-rises.webp","gameType":"SLOT","order":51,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"59","gameName":"Ninja vs Samurai","imgUrl":"https://status-res.askmebet.com/pg_slot/ninja-vs-samurai.webp","gameType":"SLOT","order":52,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"61","gameName":"Flirting Scholar","imgUrl":"https://status-res.askmebet.com/pg_slot/flirting-scholar.webp","gameType":"SLOT","order":53,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"43","gameName":"Three Monkeys","imgUrl":"https://status-res.askmebet.com/pg_slot/three-monkeys.webp","gameType":"SLOT","order":57,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"44","gameName":"Emperor's Favour","imgUrl":"https://status-res.askmebet.com/pg_slot/emperors-favour.webp","gameType":"SLOT","order":58,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"41","gameName":"Symbols of Egypt","imgUrl":"https://status-res.askmebet.com/pg_slot/symbols-of-egypt.webp","gameType":"SLOT","order":59,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"39","gameName":"Piggy Gold","imgUrl":"https://status-res.askmebet.com/pg_slot/piggy-gold.webp","gameType":"SLOT","order":60,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"38","gameName":"Gem Saviour Sword","imgUrl":"https://status-res.askmebet.com/pg_slot/gem-saviour-sword.webp","gameType":"SLOT","order":61,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"31","gameName":"Baccarat Deluxe","imgUrl":"https://status-res.askmebet.com/pg_slot/baccarat-deluxe.webp","gameType":"SLOT","order":62,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"37","gameName":"Santa's Gift Rush","imgUrl":"https://status-res.askmebet.com/pg_slot/santas-gift-rush.webp","gameType":"SLOT","order":63,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"33","gameName":"Hip Hop Panda","imgUrl":"https://status-res.askmebet.com/pg_slot/hip-hop-panda.webp","gameType":"SLOT","order":64,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"36","gameName":"Prosperity Lion","imgUrl":"https://status-res.askmebet.com/pg_slot/prosperity-lion.webp","gameType":"SLOT","order":65,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"34","gameName":"Legend of Hou Yi","imgUrl":"https://status-res.askmebet.com/pg_slot/legend-of-hou-yi.webp","gameType":"SLOT","order":67,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"35","gameName":"Mr. Hallow-Win","imgUrl":"https://status-res.askmebet.com/pg_slot/mr-hallow-win.webp","gameType":"SLOT","order":68,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"29","gameName":"Dragon Legend","imgUrl":"https://status-res.askmebet.com/pg_slot/dragon-legend.webp","gameType":"SLOT","order":69,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"28","gameName":"Hotpot","imgUrl":"https://status-res.askmebet.com/pg_slot/hotpot.webp","gameType":"SLOT","order":70,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"18","gameName":"Hood vs Wolf","imgUrl":"https://status-res.askmebet.com/pg_slot/hood-wolf.webp","gameType":"SLOT","order":71,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"2","gameName":"Gem Saviour","imgUrl":"https://status-res.askmebet.com/pg_slot/gem-saviour.webp","gameType":"SLOT","order":74,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"17","gameName":"Wizdom Wonders","imgUrl":"https://status-res.askmebet.com/pg_slot/wizdom-wonders.webp","gameType":"SLOT","order":75,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"7","gameName":"Medusa","imgUrl":"https://status-res.askmebet.com/pg_slot/medusa.webp","gameType":"SLOT","order":79,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"6","gameName":"Medusa II","imgUrl":"https://status-res.askmebet.com/pg_slot/medusa2.webp","gameType":"SLOT","order":82,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"24","gameName":"Win Win Won","imgUrl":"https://status-res.askmebet.com/pg_slot/win-win-won.webp","gameType":"SLOT","order":83,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"1","gameName":"Honey Trap of Diao Chan","imgUrl":"https://status-res.askmebet.com/pg_slot/diaochan.webp","gameType":"SLOT","order":84,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"20","gameName":"Reel Love","imgUrl":"https://status-res.askmebet.com/pg_slot/reel-love.webp","gameType":"SLOT","order":85,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"69","gameName":"Bikini Paradise","imgUrl":"https://status-res.askmebet.com/pg_slot/bikini-paradise.webp","gameType":"SLOT","order":86,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"26","gameName":"Tree of Fortune","imgUrl":"https://status-res.askmebet.com/pg_slot/fortune-tree.webp","gameType":"SLOT","order":90,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"3","gameName":"Fortune Gods","imgUrl":"https://status-res.askmebet.com/pg_slot/fortune-gods.webp","gameType":"SLOT","order":91,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"50","gameName":"Journey to the Wealth","imgUrl":"https://status-res.askmebet.com/pg_slot/journey-to-the-wealth.webp","gameType":"SLOT","order":92,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"63","gameName":"Dragon Tiger Luck","imgUrl":"https://status-res.askmebet.com/pg_slot/dragon-tiger-luck.webp","gameType":"SLOT","order":93,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"40","gameName":"Jungle Delight","imgUrl":"https://status-res.askmebet.com/pg_slot/jungle-delight.webp","gameType":"SLOT","order":94,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"62","gameName":"Gem Saviour Conquest","imgUrl":"https://status-res.askmebet.com/pg_slot/gem-saviour-conquest.webp","gameType":"SLOT","order":95,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"85","gameName":"Genie's 3 Wishes","imgUrl":"https://status-res.askmebet.com/pg_slot/genies-wishes.webp","gameType":"SLOT","order":97,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"80","gameName":"Circus Delight","imgUrl":"https://status-res.askmebet.com/pg_slot/circus-delight.webp","gameType":"SLOT","order":98,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"58","gameName":"Vampire's Charm","imgUrl":"https://status-res.askmebet.com/pg_slot/vampires-charm.webp","gameType":"SLOT","order":99,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"88","gameName":"Jewels of Prosperity","imgUrl":"https://status-res.askmebet.com/pg_slot/jewels-prosper.webp","gameType":"SLOT","order":100,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"64","gameName":"Muay Thai Champion","imgUrl":"https://status-res.askmebet.com/pg_slot/muay-thai-champion.webp","gameType":"SLOT","order":101,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"97","gameName":"Jack Frost's Winter","imgUrl":"https://status-res.askmebet.com/pg_slot/jack-frosts.webp","gameType":"SLOT","order":102,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"86","gameName":"Galactic Gems","imgUrl":"https://status-res.askmebet.com/pg_slot/galactic-gems.webp","gameType":"SLOT","order":103,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"94","gameName":"Bali Vacation","imgUrl":"https://status-res.askmebet.com/pg_slot/bali-vacation.webp","gameType":"SLOT","order":104,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"101","gameName":"Rise of Apollo","imgUrl":"https://status-res.askmebet.com/pg_slot/rise-of-apollo.webp","gameType":"SLOT","order":105,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"109","gameName":"Sushi Oishi","imgUrl":"https://status-res.askmebet.com/pg_slot/sushi-oishi.webp","gameType":"SLOT","order":106,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"67","gameName":"Shaolin Soccer","imgUrl":"https://status-res.askmebet.com/pg_slot/shaolin-soccer.webp","gameType":"SLOT","order":107,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"91","gameName":"Guardians of Ice and Fire","imgUrl":"https://status-res.askmebet.com/pg_slot/gdn-ice-fire.webp","gameType":"SLOT","order":108,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"93","gameName":"Opera Dynasty","imgUrl":"https://status-res.askmebet.com/pg_slot/opera-dynasty.webp","gameType":"SLOT","order":109,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"103","gameName":"Crypto Gold","imgUrl":"https://status-res.askmebet.com/pg_slot/crypto-gold.webp","gameType":"SLOT","order":110,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"42","gameName":"Ganesha Gold","imgUrl":"https://status-res.askmebet.com/pg_slot/ganesha-gold.webp","gameType":"SLOT","order":111,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"70","gameName":"Candy Burst","imgUrl":"https://status-res.askmebet.com/pg_slot/candy-burst.webp","gameType":"SLOT","order":112,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"100","gameName":"Candy Bonanza","imgUrl":"https://status-res.askmebet.com/pg_slot/candy-bonanza.webp","gameType":"SLOT","order":113,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"83","gameName":"Wild Fireworks","imgUrl":"https://status-res.askmebet.com/pg_slot/wild-fireworks.webp","gameType":"SLOT","order":114,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"105","gameName":"Heist Stakes","imgUrl":"https://status-res.askmebet.com/pg_slot/heist-stakes.webp","gameType":"SLOT","order":115,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true},{"productCode":"pgslot","seq":1,"gameId":"102","gameName":"Mermaid Riches","imgUrl":"https://status-res.askmebet.com/pg_slot/mermaid-riches.webp","gameType":"SLOT","order":116,"maintaine":false,"recommended":false,"newGame":false,"mode":1,"createDate":"2022-04-01 00:00:00","isActive":true}]}};
	const games = game_list.data.lists;


	function sliderAvanza(){
		espacioFinal = espacioDerecha - sliderWrapper.offsetWidth;
		if(espacioFinal>0){
			posicionIzq-=350;
			margenIzquierda = posicionIzq;
			espacioDerecha = anchoDinamico + posicionIzq

			ef = espacioDerecha-sliderWrapper.offsetWidth;
			contador++;
			if(ef<0){
				sliderCont.style.marginLeft=`${posicionIzq+Math.abs(ef)}px`;
			}else{
				sliderCont.style.marginLeft=`${posicionIzq}px`;
			}
		}
	}
	function sliderRetrocede(){
		if(contador >0){
			posicionIzq+=350;
			sliderCont.style.marginLeft=`${posicionIzq}px`;
			margenIzquierda = posicionIzq;
			espacioDerecha = anchoDinamico + posicionIzq
			contador--;
		}
	}

	function randomBetween(min, max) { // min and max included
		return Math.floor(Math.random() * (max - min + 1) + min)
	}

	var sliderCont= document.getElementById("sliderBX");
	var sliderWrapper= document.getElementById("sliderBX-contenedor");
	var slider= document.querySelectorAll(".sliderBXcell");
	var btnNext = document.querySelector("#btnSliderNext");
	var btnPrev = document.querySelector("#btnSliderPrev");
	//Inicializamos variables numéricas
	var contador = 0,posicionIzq = 0,espacioDerecha = 0,anchoDinamico = 0,margenIzquierda = 0,espacioDerecha, ef= 0;
	var numSliders = slider.length;

	const run = async () => {
		let count = 1;
		setInterval(() => {
			if(count % 10) {
				//console.log(count);
				clickNext();
			} else {
				clickPrev();
				count = 0;
			}
			count++;

		}, 2000);

		// console.log(resp)
		let all_games = games.หยิบ(30)
		let j = 30;
		let ten_games = [];
		all_games.forEach(function(game) {
			// code
			//console.log(game.recommended)
			// if(game.recommended)
			ten_games.push(game);
		});

		let dom = '';
		for(let i = 0; i<30 ; i++) {
			let jackpots = ['6125','5920','4800','1800','995','960','690','520','510','500'];
			let gameName = ten_games[i].gameName;
			let gameId = ten_games[i].gameId;
			let gameImg = ten_games[i].imgUrl;
			let gameUrl = `https://member.aba444.com/home/play_game/pg_game/${gameId}?title=${encodeURIComponent(gameName)}`;
			if(!jackpots[i])
				jackpots[i] = randomBetween(100,200);
			dom += `<div class="sliderBXcell">
            <a target="_blank" href="${gameUrl}"><img class="game-img" src="${gameImg}" /></a>
            <h3 class="game-title">${gameName}</h3>
            <a target="_blank" href="${gameUrl}"><h4 class="game-jackpot">+${parseInt(jackpots[i]) + randomBetween(100,200)} บาท</h2></a>
            <p class="game-phone">${randomPhone()}</p>
            </div>`
		}
		append("sliderBX",dom);

		var sliderCont= document.getElementById("sliderBX");
		var sliderWrapper= document.getElementById("sliderBX-contenedor");
		var slider= document.querySelectorAll(".sliderBXcell");
		var btnNext = document.querySelector("#btnSliderNext");
		var btnPrev = document.querySelector("#btnSliderPrev");

		//Inicio al encontrar el elemento #sliderCont
		if(sliderCont){
			// console.log("slider[0].offsetWidth",slider[0].offsetWidth);
			espacioDerecha= (slider[0].offsetWidth) * (slider.length);
			anchoDinamico =  (slider[0].offsetWidth) * (slider.length);
			sliderCont.style.width= (anchoDinamico + 100)+"px";
			btnNext.addEventListener("click", sliderAvanza);
			btnPrev.addEventListener("click", sliderRetrocede);
		}
	}

	run();


	function clickNext() {
		document.getElementById("btnSliderNext").click();
	}

	function clickPrev() {
		document.getElementById("btnSliderPrev").click();
	}

	function randomPhone() {
		let a = ['081','064','091','088','092','066','093','089','067','099'];
		let b = ['152','523','242','564','998','092','995','254','127','668'];
		let c = "⭐⭐⭐⭐";

		return `${a.หยิบ(1)[0]}-${b.หยิบ(1)[0]}-${c}`;
	}



	function randomJackpot() {
		let jackpots = ['6,125','5,920','4,800','1,800','995','960','690','520','510','500'];
		// let a = ['6,125','5,920','4,800','1,800','995','960','690','520','510','500'];
		return `${jackpots.หยิบ(1)[0]}`;
	}

	function append(id,dom) {
		document.getElementById(id).innerHTML = dom;
	}


</script>
<section class="jackpot">
	<span class="mb-4 mt-1" style="font-size: 25px"><i class="fa fa-gift"></i>&nbsp;Jackpot</span>
</section>
<div id="sliderBX-contenedor">
	<div id="btnSliderNext"><i class="fa fa-angle-right" aria-hidden="true"></i></div>
	<div id="btnSliderPrev"><i class="fa fa-angle-left" aria-hidden="true"></i></div>
	<div id="sliderBX">

	</div>
</div>
<hr style="margin-top: 15px">
<section class="game-trans">
	<div class="games-main">
		<div class="games-nav mt-3 mb-3">
			<div id="SlotIcon" class="games-nav-list games-nav-list-first" onclick="Open('Slot')">
				<img id="nav_slot" src="<?php echo base_url(); ?>assets/images/nav-slot-red.png" class="w-100 pointer" />
			</div>
			<div id="CasinoIcon" class="games-nav-list games-nav-list-first" onclick="Open('Casino')">
				<img id="nav_casino" src="<?php echo base_url(); ?>assets/images/nav-casino.png" class="w-100 pointer" />
			</div>
			<div id="FootballIcon" class="games-nav-list games-nav-list-last" onclick="Open('Football')">
				<img id="nav_sport" src="<?php echo base_url(); ?>assets/images/nav-sport.png" class="w-100 pointer" />
			</div>
			<div id="LottoIcon" class="games-nav-list games-nav-list-last" onclick="Open('Lotto')">
				<img id="nav_lotto" src="<?php echo base_url(); ?>assets/images/nav-huay.png" class="w-100 pointer" />
			</div>
			<div class="clearfix"></div>
		</div>
	</div>
	<div class="all-games">
		<div class="play">
			<div>
				<div id="Slot">
					<figure id="pg">
						<a href="<?php echo base_url('lobby/pg_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/pgbt.jpg"
																			   style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="live22">
						<a href="<?php echo base_url('lobby/live22_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/live22bt.jpg"
																				   style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="spg">
						<a href="<?php echo base_url('lobby/spg_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/spbt.jpg"
																				style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="ganapati">
						<a href="<?php echo base_url('lobby/ganapati_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/gmtbt.jpg"
																					 style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="ameba">
						<a href="<?php echo base_url('lobby/ameba_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/ambabt.jpg"
																				  style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="askmebetslot">
						<a href="<?php echo base_url('lobby/askmebetslot_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/dgsbt.jpg"
																						 style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="slotxo">
						<a href="<?php echo base_url('lobby/slotxo_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/xobt.jpg"
																				   style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="ambgame">
						<a href="<?php echo base_url('lobby/ambgame_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/ambbt.jpg"
																					style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="evoplay">
						<a href="<?php echo base_url('lobby/evoplay_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/evoplaybt.jpg"
																					style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="pragmaticslot">
						<a href="<?php echo base_url('lobby/pragmaticslot_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/pragmaticslotbt.jpg"
																						  style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure  id="ambslot">
						<a href="<?php echo base_url('lobby/ambslot_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/ambslotbt.jpg"
																					style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="allwayspin">
						<a href="<?php echo base_url('lobby/allwayspin_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/allwayspinbt.jpg"
																					   style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="booongo">
						<a href="<?php echo base_url('lobby/booongo_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/booongobt.jpg"
																					style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="funkygame">
						<a href="<?php echo base_url('lobby/funkygame_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/funkygamebt.jpg"
																					  style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="funtagaming">
						<a href="<?php echo base_url('lobby/funtagaming_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/funtabt.jpg"
																						style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="iconicgaming">
						<a href="<?php echo base_url('lobby/iconicgaming_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/iconicbt.jpg"
																						 style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="kagaming">
						<a href="<?php echo base_url('lobby/kagaming_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/kabt.jpg"
																					 style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="mannaplay">
						<a href="<?php echo base_url('lobby/mannaplay_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/mannaplaybt.jpg"
																					  style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="wazdandirect">
						<a href="<?php echo base_url('lobby/wazdandirect_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/wazdandirectbt.jpg"
																						 style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="hotgraph">
						<a target="_blank" href="<?php echo base_url('home/play_game_once/hotgraph_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/hotgraphbt.jpg"
																												   style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="jili">
						<a href="<?php echo base_url('lobby/jili_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/jilibt.jpg"
																				 style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="simpleplay">
						<a href="<?php echo base_url('lobby/simpleplay_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/simpleplay.png"
																					   style="width: 100%; border-radius: 10px" alt="SimplePlay Gaming"/></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="microgame">
						<a href="<?php echo base_url('lobby/microgame_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/microgame.png"
																					  style="width: 100%; border-radius: 10px" alt="Micro Gaming"/></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="yggdrasil">
						<a href="<?php echo base_url('lobby/yggdrasil_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/yggdrasil.png"
																					  style="width: 100%; border-radius: 10px" alt="Yggdrasil Gaming"/></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="upgslot">
						<a href="<?php echo base_url('lobby/upgslot_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/upgslot.png"
																					style="width: 100%; border-radius: 10px" alt="UPGslot Gaming"/></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="p8">
						<a href="<?php echo base_url('lobby/p8_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/p8.png"
																			   style="width: 100%; border-radius: 10px" alt="P8 Gaming"/></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="mpoker">
						<a href="<?php echo base_url('lobby/mpoker_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/mpoker.png"
																				   style="width: 100%; border-radius: 10px" alt="M-Poker Gaming"/></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="avgaming">
						<a target="_blank" href="<?php echo base_url('home/play_game_once/avgaming_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/avgaming.png"
																												   style="width: 100%; border-radius: 10px" alt="Cherry Gaming"/></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="dragongaming">
						<a href="<?php echo base_url('lobby/dragongaming_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/dragongaming.png"
																						 style="width: 100%; border-radius: 10px" alt="Dragon Gaming"/></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="i8">
						<a href="<?php echo base_url('lobby/i8_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/i8.png"
																			   style="width: 100%; border-radius: 10px" alt="I8 Gaming"/></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="kingmaker">
						<a href="<?php echo base_url('lobby/kingmaker_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/kingmaker.png"
																					  style="width: 100%; border-radius: 10px" alt="King Maker Gaming"/></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="kingpoker">
						<a href="<?php echo base_url('lobby/kingpoker_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/kingpoker.png"
																					  style="width: 100%; border-radius: 10px" alt="King Poker Gaming"/></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="mega7">
						<a href="<?php echo base_url('lobby/mega7_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/mega7.png"
																				  style="width: 100%; border-radius: 10px" alt="Mega7 Gaming"/></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure  id="relaxgame">
						<a href="<?php echo base_url('lobby/relaxgame_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/relaxgame.png"
																					  style="width: 100%; border-radius: 10px" alt="Relax Gaming"/></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
				</div>
				<div id="Casino" style="display: none">
					<figure id="sa">
						<a target="_blank" href="<?php echo base_url('home/play_game_once/sa_game')?>"><img class="mb-3"
																											src="<?php echo base_url(); ?>assets/images/game/sabt.jpg" style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="sexy">
						<a target="_blank" href="<?php echo base_url('home/play_game_once/sexy_game')?>"><img class="mb-3"
																											  src="<?php echo base_url(); ?>assets/images/game/sebt.jpg" style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="pretty">
						<a target="_blank" href="<?php echo base_url('home/play_game_once/pretty_game')?>"><img class="mb-3"
																												src="<?php echo base_url(); ?>assets/images/game/ptbt.jpg" style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="ag">
						<a target="_blank" href="<?php echo base_url('home/play_game_once/ag_game')?>"><img class="mb-3"
																											src="<?php echo base_url(); ?>assets/images/game/agbt.jpg" style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure  id="dream">
						<a target="_blank" href="<?php echo base_url('home/play_game_once/dream_game')?>"><img class="mb-3"
																											   src="<?php /*echo base_url(); */?>assets/images/game/dgbt.jpg" style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure  id="allbet">
						<a target="_blank" href="<?php echo base_url('home/play_game_once/allbet_game')?>"><img class="mb-3"
																												src="<?php /*echo base_url(); */?>assets/images/game/allbetbt.jpg" style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="ebet">
						<a target="_blank" href="<?php echo base_url('home/play_game_once/ebet_game')?>"><img class="mb-3"
																											  src="<?php /*echo base_url(); */?>assets/images/game/ebetbt.jpg" style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure  id="bg">
						<a target="_blank" href="<?php echo base_url('home/play_game_once/bg_game')?>"><img class="mb-3"
																											src="<?php /*echo base_url(); */?>assets/images/game/bgcasinobt.jpg" style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="pragmatic">
						<a target="_blank" href="<?php echo base_url('home/play_game_once/pragmatic_game')?>"><img class="mb-3"
																												   src="<?php /*echo base_url(); */?>assets/images/game/pragmaticbt.jpg" style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="greendragon">
						<a target="_blank" href="<?php echo base_url('home/play_game_once/greendragon_game')?>"><img class="mb-3"
																													 src="<?php /*echo base_url(); */?>assets/images/game/greendgbt.jpg" style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="betgame">
						<a target="_blank" href="<?php echo base_url('home/play_game_once/betgame_game')?>"><img class="mb-3"
																												 src="<?php /*echo base_url(); */?>assets/images/game/betgamebt.jpg" style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
					<figure id="keno">
						<a target="_blank" href="<?php echo base_url('home/play_game_once/keno_game')?>"><img class="mb-3"
																											  src="<?php /*echo base_url(); */?>assets/images/game/kenobt.jpg" style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>

				</div>
				<div id="Football" style="display: none">
					<figure id="ambbet">
						<a href="<?php echo base_url('home/play_game_once/ambbet') ?>"
						   target="_blank"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/ambbetbt.jpg"
												style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
				</div>
				<div id="Lotto" style="display: none">
					<figure id="lotto">
						<a href="<?php echo base_url('home/play_game_once/lotto_game') ?>"
						   target="_blank"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/amblottobt.jpg"
												style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text"><?php echo $this->lang->line('close'); ?></div>
						</div>
					</figure>
				</div>
			</div>
		</div>
	</div>
	<script>
		let slot_status = "<?php echo $web_setting['slot_status']['value']?>";
		let casino_status = "<?php echo $web_setting['casino_status']['value']?>";
		let football_status = "<?php echo $web_setting['football_status']['value']?>";
		let lotto_status = "<?php echo $web_setting['lotto_status']['value']?>";
		let result_alert = [];
		// console.log("slot_status :",slot_status);
		// console.log("casino_status :",casino_status);
		// console.log("football_status :",football_status);
		// console.log("lotto_status",lotto_status);
		window.onload = function() {
			if (slot_status == false){
				document.getElementById("SlotIcon").style.display='none';
				document.getElementById("Slot").style.display='none';
				result_alert.push("สล็อตออนไลน์");
			}if (casino_status == false){
				document.getElementById("CasinoIcon").style.display='none';
				document.getElementById("Casino").style.display='none';
				result_alert.push("คาสิโนสด");
			}if (football_status == false){
				document.getElementById("FootballIcon").style.display='none';
				document.getElementById("Football").style.display='none';
				result_alert.push("เดิมพันกีฬา");
			}if (lotto_status == false){
				document.getElementById("LottoIcon").style.display='none';
				document.getElementById("Lotto").style.display='none';
				result_alert.push("หวย")
			}if(result_alert.length > 0){
				// console.log("typeof",typeof(result_alert));
				// console.log("result :",result_alert);
				// sweetAlert2('warning', 'ขณะนี้มีเกมส์กำลังปิดใช้งานดังนี้ :'+ result_alert);
			}
		};
		function Open(type) {
			if (type == "Slot") {
				$("#Slot").fadeIn();
				$("#Casino").hide();
				$("#Football").hide();
				$("#Lotto").hide();

				$("#nav_slot").attr("src", "<?php echo base_url() ?>assets/images/nav-slot-red.png");
				$("#nav_casino").attr("src", "<?php echo base_url() ?>assets/images/nav-casino.png");
				$("#nav_sport").attr("src", "<?php echo base_url() ?>assets/images/nav-sport.png");
				$("#nav_lotto").attr("src", "<?php echo base_url() ?>assets/images/nav-huay.png");
			} else if (type == "Casino") {
				$("#Slot").hide();
				$("#Casino").fadeIn();
				$("#Football").hide();
				$("#Lotto").hide();

				$("#nav_slot").attr("src", "<?php echo base_url() ?>assets/images/nav-slot.png");
				$("#nav_casino").attr("src", "<?php echo base_url() ?>assets/images/nav-casino-red.png");
				$("#nav_sport").attr("src", "<?php echo base_url() ?>assets/images/nav-sport.png");
				$("#nav_lotto").attr("src", "<?php echo base_url() ?>assets/images/nav-huay.png");
			}else if (type == "Lotto") {
				$("#Slot").hide();
				$("#Lotto").fadeIn();
				$("#Football").hide();
				$("#Casino").hide();

				$("#nav_slot").attr("src", "<?php echo base_url() ?>assets/images/nav-slot.png");
				$("#nav_casino").attr("src", "<?php echo base_url() ?>assets/images/nav-casino.png");
				$("#nav_sport").attr("src", "<?php echo base_url() ?>assets/images/nav-sport.png");
				$("#nav_lotto").attr("src", "<?php echo base_url() ?>assets/images/nav-huay-red.png");
			} else {
				$("#Slot").hide();
				$("#Casino").hide();
				$("#Lotto").hide();
				$("#Football").fadeIn();

				$("#nav_slot").attr("src", "<?php echo base_url() ?>assets/images/nav-slot.png");
				$("#nav_casino").attr("src", "<?php echo base_url() ?>assets/images/nav-casino.png");
				$("#nav_sport").attr("src", "<?php echo base_url() ?>assets/images/nav-sport-red.png");
				$("#nav_lotto").attr("src", "<?php echo base_url() ?>assets/images/nav-huay.png");
			}
		}
	</script>
</section>
<style>
	.game-title {
		display: none;
		font-size: 36px;
		margin: 0;
	// font-weight: bold;

	}
	.game-jackpot {
		display: inline-block;
		padding: 5px 10px;
		font-weight: 500;
		color: #fff;
		background: linear-gradient(180deg,#91f27f 10%,#00b302);
		border-radius: 15px;
		margin: 5px;
		font-size: 30px;
	}
	.game-phone {
		margin: 0;
		font-size: 18px;
	}
	.game-img {
max-width: 85px;
    width: 85px;
    height: 120px;
    border-radius: 10px;
    // margin-top: 5px;
}
	body{
	// background:black;
		margin:0px;
		height:0px;
	}
	#sliderBX{
		font-family: Arial;
		height:130px;
		background:#ffe665;
		margin-left:0px;
		transition:margin .2s ease-in-out;
	}
	#sliderBX-contenedor{
		width:100%;
		overflow-x:hidden;
		overflow-y:hidden;
		position:relative;
	// background:blue;
		margin: 0px;
		box-shadow: rgb(0 0 0 / 35%) 0px 5px 15px;
    border-radius: 10px;
	}
	#btnSliderNext i, #btnSliderPrev i{
		padding-top:6px;
	}
	#btnSliderNext, #btnSliderPrev{
		position:absolute;
		top:50%;
		transform:translateY(-50%);
		background:#2997AD;
		color:#fff;
		width:30px;
		height:30px;
		border-radius:15px;
		padding:0px;
		text-align:center;
		opacity:.5;
	}
	#btnSliderNext:hover, #btnSliderPrev:hover{
		cursor:pointer;
		background:black;
	}
	#btnSliderNext{
		position:absolute;
		right:5px;
	}
	#btnSliderPrev{
		position:absolute;
		left:5px;
	}
	.sliderBXcell{
		width: 28vw;
		height:130px;
		display:inline-block;
		float:left;
		text-align:center;
	}
	.sliderBXcell p{
	// padding-top:10px;
	}
	#sliderBX .sliderBXcell:nth-child(odd){
		height:100px;
		background:#ffe665;
		display:inline-block;
		float:left;
		padding: 5px 10px;
	}
	#sliderBX .sliderBXcell:nth-child(even){
		height:130px;
		background:#ffc749;
		display:inline-block;
		float:left;
		padding: 5px 10px;
	}
	p{
		margin:0px;
		padding:0px;
	}
	/* Extra small devices (phones, 600px and down) */
	@media only screen and (max-width: 600px) {
		.game-jackpot {
			font-size: 14px;
			padding: 1px 4px;
		}
		.game-title{
			font-size: 16px;
		}
		.game-phone{
			font-size: 14px;
		}
		.sliderBXcell{
			width: 25vw;
		}
		#sliderBX .sliderBXcell:nth-child(even){
			height: 200px;
		}
		#sliderBX .sliderBXcell:nth-child(odd){
			height: 200px;
		}
		.game-img {
			float: none;
		}
	}
	/* Extra small devices (phones, 600px and down) */
	@media only screen and (min-width: 600px) {
		.game-jackpot {
			font-size: 14px;
			padding: 1px 4px;
		}
		.game-title{
			font-size: 16px;
			display: none;
		}
		.game-phone{
			font-size: 14px;
		}
		.sliderBXcell{
			width: 25vw;
		}
		.game-img {
			float: left;
		}
	}
	/* Extra small devices (phones, 600px and down) */
	@media only screen and (min-width: 672px) {
		.game-jackpot {
			font-size: 14px;
			padding: 5px 9px;
		}
		.game-title{
			display: block;
			font-size: 16px;
		}
		.game-phone{
			font-size: 14px;
		}
		.sliderBXcell{
			width: 25vw;
		}
		.game-img {
			float: left;
		}
	}
	/* Medium devices (landscape tablets, 768px and up) */
	@media only screen and (min-width: 768px) {
		.game-jackpot {
			font-size: 18px;
    padding: 5px 9px;
    text-align: center;
		}
		.game-title{
			font-size: 20px;
		}
		.game-phone{
			font-size: 20px;
		}
	}
	/* Large devices (laptops/desktops, 992px and up) */
	@media only screen and (min-width: 992px) {
		.game-jackpot {
			font-size: 28px;
		}
	}

	/* Extra small devices (phones, 415px and down) */
	@media only screen and (max-width: 415px) {
		.game-jackpot {
			font-size: 14px;
			padding: 5px 9px;
		}
		.game-title{
			font-size: 16px;
		}
		.game-phone{
			font-size: 14px;
		}
		.sliderBXcell{
			width: 27vw;
		}
		#sliderBX .sliderBXcell:nth-child(even){
			height: 242px;
			    height: 220px !important;
		}
		#sliderBX .sliderBXcell:nth-child(odd){
			//height: 242px;
			    height: 220px !important;
		}
	}
</style>
