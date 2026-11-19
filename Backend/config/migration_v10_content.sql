-- ══════════════════════════════════════════════════════════════
-- Migration V10: FAQ copy matches what the farm actually sells.
--
-- The seeded questions still promised day-old chicks, broiler
-- breeds and a hatch-rate guarantee. The farm sells farm-fresh eggs,
-- layer feeds and organic manure/folia, so those answers are
-- rewritten (and chick questions answered honestly) instead of
-- advertising products that are not available yet.
-- ══════════════════════════════════════════════════════════════

UPDATE cms_faq_items SET
    category = 'Products & Orders',
    question = 'What products do you sell?',
    answer = 'We sell farm-fresh table eggs in 30-egg trays, six stages of layer feed (chick mash, growers mash, developers mash, pre-lay mash, layer mash 1 and layer mash 2) in 50kg bags, organic manure in 50kg and 20kg bags, and Organic Folia in 20L, 10L and 5L containers.'
WHERE question = 'What breeds of chickens do you offer?';

UPDATE cms_faq_items SET
    category = 'Products & Orders',
    question = 'How fresh are your eggs?',
    answer = 'Eggs are collected, graded and packed into 30-egg trays the same day they are laid. Trays can be collected at either of our farms, or delivered to homes, shops, hotels and bakeries across Busia and western Kenya.'
WHERE question = 'Are your chicks vaccinated?';

UPDATE cms_faq_items SET
    category = 'Products & Orders',
    question = 'Which feed should I buy for my flock?',
    answer = 'Follow the age of your birds: chick mash from 0 to 8 weeks, growers mash for weeks 9 to 12, developers mash for weeks 13 to 16, pre-lay mash for weeks 17 to 22, layer mash 1 for weeks 23 to 32, and layer mash 2 from week 33 to the end of lay.'
WHERE question = 'What is the difference between broilers and layers?';

UPDATE cms_faq_items SET
    category = 'Products & Orders',
    question = 'Do you sell day-old chicks?',
    answer = 'Not yet. Our current focus is farm-fresh eggs, layer feeds and organic manure and folia. Talk to us about your plans and we will let you know as soon as chicks become available.'
WHERE question = 'How do I care for day-old chicks?';

UPDATE cms_faq_items SET
    category = 'Products & Orders',
    question = 'Can I visit the farm before ordering?',
    answer = 'Yes. You are welcome at both our Nasira farm and our Tangakona farm during working hours. Call ahead on +254 727 585 599 so someone is available to walk you through the layer houses, feed mill and grading area.'
WHERE question = 'What is the hatch rate guarantee?';

UPDATE cms_faq_items SET
    question = 'When should I change feed stage?',
    answer = 'Move from chick mash to growers mash at 9 weeks, to developers mash at 13 weeks, to pre-lay mash at 17 weeks, to layer mash 1 at 23 weeks, and to layer mash 2 at 33 weeks. Each change matches the birds'' calcium and protein needs at that age.'
WHERE question = 'When should I switch from starter to grower feed?';

UPDATE cms_faq_items SET
    question = 'How much feed does a layer need daily?',
    answer = 'Roughly 30-50g a day for chicks, 60-80g for growers, and 110-120g for a mature layer at peak production. Consumption rises with body weight and heat, so keep feed and clean water available at all times.'
WHERE question = 'How much feed does a chicken need daily?';

UPDATE cms_faq_items SET
    answer = 'If something arrives damaged or short, tell us within 24 hours of delivery with a photo and we will replace it. Eggs are replaced for breakage in transit, feeds for any quality issue, and manure or folia for damaged or leaking containers.'
WHERE question = 'What is your return and refund policy?';

UPDATE cms_faq_items SET
    answer = 'For bulk orders from established customers (50+ feed bags or 100+ egg trays) we offer 2-week payment terms with an agreed order form.'
WHERE question = 'Do you offer payment plans?';

UPDATE cms_faq_items SET
    question = 'Is there a warranty on your products?',
    answer = 'Yes. Eggs are guaranteed fresh when they reach you and replaced if they are broken in transit. Feeds are milled to our published formula and replaced if a batch is faulty. Organic manure and folia are replaced if a container is damaged on delivery.'
WHERE question = 'Is there a warranty on the chicks?';
