@php
    $guideLinks = [
        'page' => 'https://www.facebook.com/pages/create/',
        'business' => 'https://business.facebook.com/',
        'accounts' => 'https://business.facebook.com/settings/ad-accounts/',
        'billing' => 'https://adsmanager.facebook.com/billing/',
        'security' => 'https://accountscenter.facebook.com/password_and_security/two_factor/',
        'instagram' => 'https://business.facebook.com/settings/instagram-accounts/',
        'manager' => 'https://adsmanager.facebook.com/',
        'events' => 'https://business.facebook.com/events_manager2/',
        'support' => 'https://business.facebook.com/business-support-home/',
        'connect' => route('marketplace.vendor.meta-ads.connection'),
        'campaign' => route('marketplace.vendor.meta-ads.campaigns.create'),
    ];
    $phoneGuides = [
        'en' => [
            'title' => 'Run your first ad from your phone',
            'intro' => 'Only have a Facebook profile? Start with setup, then return here to create your ad.',
            'phone' => 'Use Chrome or Safari and the same Facebook login throughout. If a setup screen is missing: Chrome ⋮ → Desktop site; Safari aA / page menu → Request Desktop Website. Meta links open in a new tab; come back here after each step. Menu names may vary by account.',
            'link' => 'Open', 'external' => 'opens in a new tab',
            'sections' => [
                ['Basic setup · do once', [
                    ['Create your business Page', 'Enter your business name, category, phone and address. Your personal profile alone cannot publish these ads.', 'page', 'Create Facebook Page'],
                    ['Create a business portfolio', 'Open Meta Business Suite → create a business portfolio, if prompted. Add your Page under Settings → Accounts → Pages. You need full control of these assets.', 'business', 'Open Business Suite'],
                    ['Create an ad account', 'Settings → Accounts → Ad accounts → Add → Create a new ad account. Choose your business, INR and the correct time zone carefully, then assign yourself access. If creation is unavailable, follow Meta’s account eligibility prompts.', 'accounts', 'Open ad account settings'],
                    ['Add payment and secure your login', 'Select the correct ad account → Payment settings → Add payment method. Use an available method and pay any outstanding balance. Also enable two-factor authentication.', 'billing', 'Set up billing', 'security', 'Enable two-factor authentication'],
                    ['Instagram is optional', 'For Instagram identity, connect your professional Instagram account in business settings. You can start with your Facebook Page; Instagram is not required for the first ad.', 'instagram', 'Connect Instagram'],
                ]],
                ['Create your first ad in Growbiz', [
                    ['Connect Facebook', 'Tap Connect with Facebook, sign in as the Page/ad account owner, grant the requested business permissions, and select the correct ad account and Page. If no account appears, finish setup above and reconnect.', 'connect', 'Connect in Growbiz'],
                    ['Create a campaign', 'Tap New Campaign. For a first store-link ad, choose Traffic and give it a clear name. Awareness is for reach; Sales needs working purchase tracking. Start with a small daily budget you can afford, such as ₹200–₹500.', 'campaign', 'New Campaign'],
                    ['Choose audience and schedule', 'Open the campaign → Create Ad Set. Set your daily/lifetime budget where offered, dates, local area and suitable age range. Keep targeting simple; check the estimated delivery before saving. Campaign = goal; Ad Set = audience/budget; Ad = image/message.', null, null],
                    ['Add your image and store link', 'Open the ad set → Create Ad. Upload a clear product/offer image, add Primary Text and Headline, paste your full Growbiz store/product URL in the destination link, and choose a suitable action. This dashboard supports single-image ads; use Ads Manager for video, carousel or WhatsApp-specific campaigns.', 'manager', 'Open Ads Manager'],
                    ['Check and switch on', 'Preview the ad and check the link and offer. Saving alone does not make it live. If shown as a local draft, use Push to Meta in order: Campaign → Ad Set → Ad. Activate all three levels when ready to spend. Check Meta’s review/delivery status; approval is not instant.', null, null],
                ]],
                ['Advanced settings · after your first ad', [
                    ['Track enquiries and purchases properly', 'Traffic clicks are not confirmed sales or physical visits. Before choosing Sales, ask Growbiz support to confirm your store’s Pixel/dataset and purchase events. Use Events Manager to test events; a store URL alone does not install tracking.', 'events', 'Open Events Manager'],
                    ['Improve one thing at a time', 'Compare two images/offers with similar budgets. Add UTM tags to your destination URL for analytics. Judge cost per genuine enquiry/order, not likes alone. Use Ads Manager for retargeting, custom audiences and advanced placements when eligible.', 'manager', 'Advanced controls'],
                    ['Check spend and results', 'Select the date range on this dashboard. Review spend, reach, clicks and genuine enquiries over several days before changing the ad. Set a budget/end date and an account spending limit in Meta where available. Daily budgets are averages, not a strict daily spending ceiling.', 'billing', 'Billing and spending controls'],
                ]],
                ['If your ad is not running', [
                    ['Check connection, payment and review', 'Reconnect if your token expired; confirm the selected Page and ad account. Check billing, all three Active statuses, dates, and any rejection/restriction in Meta. Read the reason before editing or requesting review. If Connect is unavailable, ask Growbiz support to check the integration.', 'support', 'Business Support Home'],
                ]],
            ],
        ],
        'hi' => [
            'title' => 'अपने फ़ोन से पहला विज्ञापन चलाएँ',
            'intro' => 'सिर्फ Facebook प्रोफ़ाइल है? पहले सेटअप करें, फिर यहाँ आकर विज्ञापन बनाएँ।',
            'phone' => 'Chrome या Safari में हर बार वही Facebook लॉगिन इस्तेमाल करें। सेटअप विकल्प न दिखे तो: Chrome ⋮ → Desktop site; Safari aA / पेज मेनू → Request Desktop Website। Meta के लिंक नए टैब में खुलेंगे; हर चरण के बाद यहाँ लौटें। खाते के अनुसार मेनू के नाम बदल सकते हैं।',
            'link' => 'खोलें', 'external' => 'नए टैब में खुलेगा',
            'sections' => [
                ['बुनियादी सेटअप · एक बार करें', [
                    ['व्यवसाय का Facebook Page बनाएँ', 'व्यवसाय का नाम, श्रेणी, फ़ोन और पता भरें। सिर्फ निजी प्रोफ़ाइल से यहाँ के विज्ञापन प्रकाशित नहीं होंगे।', 'page', 'Facebook Page बनाएँ'],
                    ['बिज़नेस पोर्टफ़ोलियो बनाएँ', 'Meta Business Suite खोलें → कहा जाए तो business portfolio बनाएँ। Settings → Accounts → Pages में अपना Page जोड़ें। इन संसाधनों पर आपका full control होना चाहिए।', 'business', 'Business Suite खोलें'],
                    ['विज्ञापन खाता बनाएँ', 'Settings → Accounts → Ad accounts → Add → Create a new ad account चुनें। अपना व्यवसाय, INR और सही time zone ध्यान से चुनें; खुद को access दें। विकल्प न मिले तो Meta की पात्रता संबंधी सूचना का पालन करें।', 'accounts', 'Ad account सेटिंग्स खोलें'],
                    ['भुगतान और सुरक्षा जोड़ें', 'सही ad account चुनें → Payment settings → Add payment method। उपलब्ध भुगतान विकल्प जोड़ें और बकाया चुकाएँ। साथ में two-factor authentication चालू करें।', 'billing', 'भुगतान सेट करें', 'security', 'दो-चरणीय सुरक्षा चालू करें'],
                    ['Instagram वैकल्पिक है', 'Instagram की पहचान से विज्ञापन चलाने के लिए professional Instagram account को business settings में जोड़ें। पहले विज्ञापन के लिए Facebook Page से शुरुआत कर सकते हैं।', 'instagram', 'Instagram जोड़ें'],
                ]],
                ['Growbiz में पहला विज्ञापन बनाएँ', [
                    ['Facebook कनेक्ट करें', 'Connect with Facebook दबाएँ। Page/ad account के मालिक वाले खाते से लॉगिन करें, माँगी गई business permissions दें और सही ad account व Page चुनें। खाता न दिखे तो ऊपर का सेटअप पूरा करके दोबारा कनेक्ट करें।', 'connect', 'Growbiz में कनेक्ट करें'],
                    ['Campaign बनाएँ', 'New Campaign दबाएँ। दुकान के लिंक पर लोगों को लाने के लिए Traffic चुनें और साफ़ नाम दें। Awareness पहुँच के लिए है; Sales के लिए खरीद की tracking सही होनी चाहिए। शुरुआत में अपनी क्षमता के अनुसार ₹200–₹500 जैसा छोटा दैनिक बजट रखें।', 'campaign', 'नया Campaign'],
                    ['ग्राहक क्षेत्र और समय चुनें', 'Campaign खोलें → Create Ad Set। जहाँ उपलब्ध हो daily/lifetime budget, तारीखें, स्थानीय क्षेत्र और उचित उम्र चुनें। शुरुआत में targeting सरल रखें; सेव करने से पहले delivery estimate देखें। Campaign = लक्ष्य; Ad Set = ग्राहक/बजट; Ad = तस्वीर/संदेश।', null, null],
                    ['तस्वीर और दुकान का लिंक डालें', 'Ad Set खोलें → Create Ad। साफ़ product/offer image, Primary Text और Headline डालें। Destination link में पूरा Growbiz store/product URL दें और सही action चुनें। यहाँ single-image ads बनते हैं; video, carousel या WhatsApp वाले campaigns के लिए Ads Manager इस्तेमाल करें।', 'manager', 'Ads Manager खोलें'],
                    ['जाँचें और चालू करें', 'Preview में तस्वीर, ऑफ़र और लिंक जाँचें। सिर्फ Save करने से विज्ञापन लाइव नहीं होता। Local draft दिखे तो क्रम से Push to Meta करें: Campaign → Ad Set → Ad। खर्च शुरू करने को तैयार हों तभी तीनों स्तर Active करें। Meta का review/delivery status देखें; मंज़ूरी तुरंत नहीं मिलती।', null, null],
                ]],
                ['एडवांस सेटिंग्स · पहले विज्ञापन के बाद', [
                    ['पूछताछ और खरीद की सही tracking', 'Traffic clicks का मतलब पक्की बिक्री या दुकान में आना नहीं है। Sales चुनने से पहले Growbiz support से अपनी दुकान का Pixel/dataset और purchase events जँचवाएँ। Events Manager में events टेस्ट करें; सिर्फ store URL देने से tracking नहीं लगती।', 'events', 'Events Manager खोलें'],
                    ['एक समय में एक सुधार करें', 'समान बजट पर दो तस्वीरों/ऑफ़र की तुलना करें। Analytics के लिए destination URL में UTM tags जोड़ें। सिर्फ likes नहीं, असली पूछताछ/ऑर्डर की लागत देखें। पात्रता होने पर retargeting, custom audiences और advanced placements Ads Manager में सेट करें।', 'manager', 'एडवांस विकल्प'],
                    ['खर्च और परिणाम देखें', 'इस dashboard में date range चुनें। बदलाव से पहले कुछ दिनों का खर्च, reach, clicks और असली पूछताछ देखें। बजट/end date रखें; Meta में उपलब्ध हो तो account spending limit भी सेट करें। Daily budget औसत है, हर दिन की सख्त खर्च सीमा नहीं।', 'billing', 'बिलिंग और खर्च नियंत्रण'],
                ]],
                ['विज्ञापन न चले तो', [
                    ['कनेक्शन, भुगतान और review जाँचें', 'Token expire हो तो फिर कनेक्ट करें; सही Page और ad account जाँचें। भुगतान, तीनों Active status, तारीखें और Meta में rejection/restriction देखें। बदलाव या review माँगने से पहले कारण पढ़ें। Connect उपलब्ध न हो तो Growbiz support से integration जँचवाएँ।', 'support', 'Business Support Home'],
                ]],
            ],
        ],
        'mr' => [
            'title' => 'मोबाइलवरून पहिली जाहिरात सुरू करा',
            'intro' => 'फक्त Facebook प्रोफाइल आहे? आधी सेटअप करा, नंतर इथे येऊन जाहिरात तयार करा.',
            'phone' => 'Chrome किंवा Safari मध्ये प्रत्येक वेळी तोच Facebook लॉगिन वापरा. सेटअपचा पर्याय दिसत नसेल तर: Chrome ⋮ → Desktop site; Safari aA / पेज मेनू → Request Desktop Website. Meta चे दुवे नवीन टॅबमध्ये उघडतील; प्रत्येक टप्प्यानंतर इथे परत या. खात्यानुसार मेनूची नावे बदलू शकतात.',
            'link' => 'उघडा', 'external' => 'नवीन टॅबमध्ये उघडेल',
            'sections' => [
                ['प्राथमिक सेटअप · एकदाच करा', [
                    ['व्यवसायाचे Facebook Page तयार करा', 'व्यवसायाचे नाव, प्रकार, फोन आणि पत्ता भरा. केवळ वैयक्तिक प्रोफाइलवरून इथल्या जाहिराती प्रकाशित करता येत नाहीत.', 'page', 'Facebook Page तयार करा'],
                    ['बिझनेस पोर्टफोलिओ तयार करा', 'Meta Business Suite उघडा → सांगितल्यास business portfolio तयार करा. Settings → Accounts → Pages मध्ये तुमचे Page जोडा. या गोष्टींवर तुमचा full control असणे आवश्यक आहे.', 'business', 'Business Suite उघडा'],
                    ['जाहिरात खाते तयार करा', 'Settings → Accounts → Ad accounts → Add → Create a new ad account निवडा. व्यवसाय, INR आणि योग्य time zone काळजीपूर्वक निवडा; स्वतःला access द्या. पर्याय उपलब्ध नसल्यास Meta च्या पात्रतेविषयीच्या सूचनांचे पालन करा.', 'accounts', 'Ad account सेटिंग्स उघडा'],
                    ['पेमेंट आणि सुरक्षा जोडा', 'योग्य ad account निवडा → Payment settings → Add payment method. उपलब्ध पेमेंट पर्याय जोडा आणि थकबाकी भरा. तसेच two-factor authentication सुरू करा.', 'billing', 'पेमेंट सेट करा', 'security', 'दोन-टप्प्यांची सुरक्षा सुरू करा'],
                    ['Instagram ऐच्छिक आहे', 'Instagram च्या नावाने जाहिरात दाखवण्यासाठी professional Instagram account business settings मध्ये जोडा. पहिल्या जाहिरातीसाठी Facebook Page वापरून सुरुवात करू शकता.', 'instagram', 'Instagram जोडा'],
                ]],
                ['Growbiz मध्ये पहिली जाहिरात तयार करा', [
                    ['Facebook कनेक्ट करा', 'Connect with Facebook दाबा. Page/ad account च्या मालकाच्या खात्यातून लॉगिन करा, मागितलेल्या business permissions द्या आणि योग्य ad account व Page निवडा. खाते दिसत नसेल तर वरील सेटअप पूर्ण करून पुन्हा कनेक्ट करा.', 'connect', 'Growbiz मध्ये कनेक्ट करा'],
                    ['Campaign तयार करा', 'New Campaign दाबा. दुकानाच्या लिंकवर ग्राहक आणण्यासाठी Traffic निवडा आणि स्पष्ट नाव द्या. Awareness पोहोच वाढवण्यासाठी आहे; Sales साठी खरेदीची tracking चालू असावी. सुरुवातीला परवडेल असे ₹200–₹500 सारखे छोटे दैनिक बजेट ठेवा.', 'campaign', 'नवीन Campaign'],
                    ['ग्राहकांचा परिसर आणि वेळ निवडा', 'Campaign उघडा → Create Ad Set. उपलब्ध ठिकाणी daily/lifetime budget, तारखा, स्थानिक परिसर आणि योग्य वयोगट निवडा. सुरुवातीला targeting सोपे ठेवा; सेव्ह करण्याआधी delivery estimate पाहा. Campaign = उद्दिष्ट; Ad Set = ग्राहक/बजेट; Ad = चित्र/संदेश.', null, null],
                    ['चित्र आणि दुकानाची लिंक द्या', 'Ad Set उघडा → Create Ad. स्पष्ट product/offer image, Primary Text आणि Headline भरा. Destination link मध्ये पूर्ण Growbiz store/product URL द्या आणि योग्य action निवडा. इथे single-image ads तयार होतात; video, carousel किंवा WhatsApp campaigns साठी Ads Manager वापरा.', 'manager', 'Ads Manager उघडा'],
                    ['तपासा आणि सुरू करा', 'Preview मध्ये चित्र, ऑफर आणि लिंक तपासा. केवळ Save केल्याने जाहिरात लाइव्ह होत नाही. Local draft दिसल्यास क्रमाने Push to Meta करा: Campaign → Ad Set → Ad. खर्च सुरू करण्यास तयार असाल तेव्हाच तिन्ही स्तर Active करा. Meta चा review/delivery status पाहा; मंजुरी लगेच मिळत नाही.', null, null],
                ]],
                ['प्रगत सेटिंग्स · पहिल्या जाहिरातीनंतर', [
                    ['चौकशी आणि खरेदीचे योग्य tracking', 'Traffic clicks म्हणजे निश्चित विक्री किंवा प्रत्यक्ष दुकानभेट नाही. Sales निवडण्याआधी Growbiz support कडून दुकानाचे Pixel/dataset आणि purchase events तपासून घ्या. Events Manager मध्ये events तपासा; फक्त store URL दिल्याने tracking बसत नाही.', 'events', 'Events Manager उघडा'],
                    ['एका वेळी एक सुधारणा करा', 'समान बजेटवर दोन चित्रांची/ऑफरची तुलना करा. Analytics साठी destination URL मध्ये UTM tags जोडा. केवळ likes ऐवजी खरी चौकशी/ऑर्डर मिळवण्याचा खर्च पाहा. पात्रतेनुसार retargeting, custom audiences व advanced placements Ads Manager मध्ये वापरा.', 'manager', 'प्रगत पर्याय'],
                    ['खर्च आणि परिणाम पाहा', 'या dashboard वर date range निवडा. बदल करण्याआधी काही दिवसांचा खर्च, reach, clicks आणि खरी चौकशी पाहा. बजेट/end date ठेवा; Meta मध्ये उपलब्ध असल्यास account spending limit सेट करा. Daily budget ही सरासरी आहे, रोजच्या खर्चाची कठोर मर्यादा नाही.', 'billing', 'बिलिंग आणि खर्च नियंत्रण'],
                ]],
                ['जाहिरात सुरू होत नसेल तर', [
                    ['कनेक्शन, पेमेंट आणि review तपासा', 'Token संपल्यास पुन्हा कनेक्ट करा; योग्य Page आणि ad account तपासा. पेमेंट, तिन्ही Active status, तारखा व Meta मधील rejection/restriction पाहा. बदल किंवा review मागण्याआधी कारण वाचा. Connect उपलब्ध नसेल तर Growbiz support कडून integration तपासून घ्या.', 'support', 'Business Support Home'],
                ]],
            ],
        ],
    ];
    $guideLanguage = in_array(app()->getLocale(), ['hi', 'mr']) ? app()->getLocale() : 'en';
@endphp
<style>
.meta-phone-guide{border:1px solid #e2e8f0;border-radius:12px;background:#fff;margin-bottom:20px;color:#243449;overflow:hidden}
.meta-phone-guide>summary{padding:16px 18px;cursor:pointer;font-weight:600;background:#f5f8ff;line-height:1.5}
.meta-phone-guide-body{padding:16px 18px}.meta-phone-guide label{font-size:13px;font-weight:600}.meta-phone-guide select{max-width:240px;min-height:44px}
.meta-phone-guide p{font-size:14px;line-height:1.65;margin:8px 0 12px}.meta-guide-phone{background:#f8fafc;border-radius:8px;padding:12px}
.meta-guide-section{border-top:1px solid #e8edf4}.meta-guide-section>summary{font-weight:600;padding:14px 0;cursor:pointer;line-height:1.5}
.meta-guide-section ol{padding-left:22px;margin:0 0 16px}.meta-guide-section li{padding:0 0 14px 5px}.meta-guide-section li:last-child{padding-bottom:0}.meta-guide-section li strong{font-size:14px}
.meta-guide-links{display:flex;gap:8px;flex-wrap:wrap}.meta-guide-links a{display:inline-flex;align-items:center;min-height:44px;padding:8px 12px;border:1px solid #d9e3f1;border-radius:8px;color:#2254a0;text-decoration:underline;font-size:13px;max-width:100%;overflow-wrap:anywhere}
.meta-phone-guide [hidden]{display:none!important}@media(max-width:575px){.meta-phone-guide-body{padding:14px}.meta-phone-guide>summary{padding:14px}.meta-guide-links a{width:100%}}
</style>
<details class="meta-phone-guide" id="meta-phone-guide" @if ($guideOpen ?? false) open @endif>
    <summary>📱 <span data-meta-guide-title>{{ $phoneGuides[$guideLanguage]['title'] }}</span> · English / हिन्दी / मराठी</summary>
    <div class="meta-phone-guide-body">
        <label for="meta-guide-language">Language / भाषा</label>
        <select id="meta-guide-language" class="form-select mb-3">
            @foreach (['en' => 'English', 'hi' => 'हिन्दी', 'mr' => 'मराठी'] as $code => $name)
                <option value="{{ $code }}" @selected($guideLanguage === $code)>{{ $name }}</option>
            @endforeach
        </select>
        @foreach ($phoneGuides as $code => $guide)
            <div data-meta-guide-language="{{ $code }}" lang="{{ $code }}" @if ($code !== $guideLanguage) hidden @endif>
                <h5>{{ $guide['title'] }}</h5>
                <p>{{ $guide['intro'] }}</p>
                <p class="meta-guide-phone">{{ $guide['phone'] }}</p>
                @foreach ($guide['sections'] as [$heading, $steps])
                    <details class="meta-guide-section" @if ($loop->first) open @endif>
                        <summary>{{ $heading }}</summary>
                        <ol>
                            @foreach ($steps as $step)
                                <li><strong>{{ $step[0] }}</strong><p>{{ $step[1] }}</p>
                                    @if ($step[2])
                                        <div class="meta-guide-links">
                                            @foreach ([2, 4] as $offset)
                                                @if (!empty($step[$offset]))
                                                    @php
                                                        $externalGuideLink = !in_array($step[$offset], ['connect', 'campaign']);
                                                    @endphp
                                                    <a href="{{ $guideLinks[$step[$offset]] }}" @if ($externalGuideLink) target="_blank" rel="noopener noreferrer" @endif>{{ $step[$offset + 1] }} @if ($externalGuideLink)<span class="visually-hidden"> ({{ $guide['external'] }})</span> ↗@endif</a>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </details>
                @endforeach
            </div>
        @endforeach
    </div>
</details>
<script>
(function () {
    var guide = document.getElementById('meta-phone-guide');
    var select = guide.querySelector('select');
    function setLanguage(code) {
        if (!['en', 'hi', 'mr'].includes(code)) return;
        select.value = code;
        guide.querySelector('[data-meta-guide-title]').textContent = guide.querySelector('[data-meta-guide-language="' + code + '"] h5').textContent;
        guide.querySelectorAll('[data-meta-guide-language]').forEach(function (panel) {
            panel.hidden = panel.dataset.metaGuideLanguage !== code;
        });
    }
    try { setLanguage(localStorage.getItem('growbiz.metaGuideLanguage') || select.value); } catch (error) {}
    select.addEventListener('change', function () {
        setLanguage(select.value);
        try { localStorage.setItem('growbiz.metaGuideLanguage', select.value); } catch (error) {}
    });
})();
</script>
