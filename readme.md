# Cloudflare Encryption Bypass 

## Steps
- Load the url in the browser.
- URL will return 503 (Service Unavailable), In the same request it will report to Cloudflare
- Now cloudflare js file from url [https://devza.com/cdn-cgi/challenge-platform/h/g/orchestrate/jsch/v1], will be injected in the DOM dynamically.
- In first request some html content was returned in response, with some JS variable as below.
```js
{
  cvId: "1",
  cType: "non-interactive",
  cNounce: "55076",
  cRay: "5e85be87ffe81a8c",
  cHash: "18f8f844ab1a9c8",
  cFPWv: "g",
  cRq: {
    ru: "aHR0cHM6Ly9kZXZ6YS5jb20vY2Z0ZXN0LnBocA==",
    ra: "TW96aWxsYS81LjAgKE1hY2ludG9zaDsgSW50ZWwgTWFjIE9TIFggMTBfMTVfNykgQXBwbGVXZWJLaXQvNTM3LjM2IChLSFRNTCwgbGlrZSBHZWNrbykgQ2hyb21lLzg2LjAuNDI0MC44MCBTYWZhcmkvNTM3LjM2",
    rm: "R0VU",
    d: "F595ezgeszFRZa7VcMt57OKKMmdGuO35EJtWTW/IOBPIsF51Umz4IJaxpfv33Mr7vqEvENb143qp122XouQOc78lNcFjtpQtvOWpZAqUnoUw6mlFVn1t9d3Bk5CqYgHmd1mQOOuSALr1SMeBlkqSe1e1X07aNFUMnA+A8fJg/IPDMkI0g/i8vLmVA+syO3spAqcL+br9pQVyfJGybhZJP4ZoUZYh3tOTX3mKm3Sc+MopDdpcNhuD3CCUK7IAzWhOpMmxS3W28XheA4lt/R3jW2xd1E1pLOCZn8xiEGX+ihIGv0YHdOGdMHsSJyUqG2sE6Jn5LCr5m6esas2j6ZRANEs+X57h4M6B9AhgjmsCefZE2olPyu5IFaS4fulpHhL+fZAU7bPSY3xq1ZxGHBopkio5D0I/k1ScejMNI9Uiq29sXnFHE5gTQXoqIUWfKwVetMovDnSQbnASwrZnDRrsvahc3GkVcUPtQUaGElToqExEwrbm7GdfSwHI5cbDY7JVjpVFW/Y48uBwTiJPyVPNyLWPjt1AzviyEsZl06Tk7qBlKe/PMvaSkGBbCECloYB8wDxRWFqQbjfNhXs81Oj4X67gIpEW36tFiKwTkAu1jlj0l/sd0yLARJgVLTEWzdo084GijkH3Z6B+cnUyqyELGDyD15nDnogLKRtqzuP9b/itLsYZwuvACqGwWesu/D9mwRD9o28dScOgz2RUIeg7nFlezTr6MJ9osMXm/hSfXT/6vQfa7EeuzHHiM//gOmZU6wCB28MmUTfv/k2fh69W1Z1gcI3Nowbw5D/aJg7qqOLhyQLmsm7DMBJgLd0N415aZ7M9eUpCjLJzE2ehc0FXfxcpCOv7zVCwH69Fy7WHI6A=",
    t: "MTYwMzczMTczOC44NzQwMDA=",
    m: "kyKBCImeGUyAJq2S3oVcJTKKa6g1736ldMUmNwlYPvA=",
    i1: "T3SUZI9fXEJwGsJ/1wvodg==",
    i2: "MKJ/t/tM+GaQVQTzg4HbdA==",
    uh: "C9nxUzqb0icqCcICD0poR378mi63eroJMgSO23tgPkw=",
    hh: "HbYuDgs8ryeluQ90odFATiSa1u+d70/EMWIsqYuQbJg=",
}
```
- Also, some form with some data was returned as below - 
```html
  <form class="challenge-form" id="challenge-form" action="/cftest.php?__cf_chl_jschl_tk__=25e5a8dbdc20eaf7511db7551d3e492b417693fb-1603731738-0-AbbebxOOAdCaeGjCDaRZF8_hgRHYnXV9o96cUZbfySgcsJMvKHm7XR0tvgthbD01R0MBnak3POeIkRgCtRqCpICR3iopR-jUBe5ad08MytFUPlUWPHsMngV3tgYMom701Hoxqm5Hby34crgf2RKtnTPIN7vez11H3Px-oy9Ajz15YOb_C4izmqI5G5ahR681md7p4oe81TMb4PDme1jBclyJug8cUF4H7BRMLbwv4aulfUhNyeo-BZJF3jHz_Yq6MKTQpBUH-too8U2PWWHM90UksO676I_prNbLEADf0lD7Qnc6wbWwHdU1g2IYfz_0Hw" method="POST" enctype="application/x-www-form-urlencoded">
    <input type="hidden" name="r" value="bece93fd894b8d7d105bdd5b77ed4f4cb77d4a72-1603731738-0-AWNOJYe1fptdTR43/DQQmL7toU0h6zOXAXRIB/X59EP3QkZx5uzOCiT8eiw3qgqZSDDc5vFhhsO0j9hB8R69g+BfO3xjTbdV2+pngsdqH5x4phomYQxURWAaBY7GPkb2DB/iXVTSDubfvFPJIU2vJqEcfl2MP4ifxxi4L9NdArF2Crj/iOJ4vu8IqKzZAcr5+SA5wXy2HhUDP0yax2d5VpD0DxJSQZhws3DTuJPUJiKqcRaaMJn1diWRwAbtamOTZLDqKonhJAzm1EEDh6bCcsuWUdEGzZnmfg/hEA10uLS4Ph7xmZXMMJvWFKPkbye5pBARQSfQ98JWmDaGrtMIGXg0/IGHixgEmW+XN4eU5+LTnrkG9vsqlo2aWwlX6uX3H0rxTdRm1OnxpEJ+OyUs683+HGaeAlbzxV4Zjik5Zv8/WjbBDzUf6p2tByOddLMWp659QInfrHCWhSO2H/Py++2KNi3xsYFuMYBeI1EvdvzXqTDyvUP+om2T4jjylTU9QlqbNgjK/L6Kzgt3qfFcE8ymA3gItXQiM0F7BtfEbIMDJQjqZKu+o4BsqyRPpXwhMl0WqgEZeDdQdJYVWTb6KMzMyyXIbu9exGH/93XKWZKOj9+swMCvEFyN0oTI/NZr+BSVaVwxkrJgyaEtHXBG01at7H1y4p5nPJL+LaDeF1gxsy8mWkjH1NEPANvu9nUT0YGrWh5bcqO6MpxzjCl0Nb4UDbdQmOgrywf6ppZuGyepk6qZFGIs/29BdsxcO8n2NpVMQWxjGLaPZ7Za70K5jhCbeJ8FiP3km/fR9EdsjDk21o4bUwSWVkvVeoO++ekrsMVivk+s24BsL7Cvcxnt36KmavJvwtHP5h6O1KLD6cnB7PwZtUDfrT6bNBqnNuDAQh4hVzhpeMCti/dHyFG4tzpwtWuXFmS6+MO/zt3K2Gr5EJlPK1SQokUd5ljtXzSPVWZuO4MWcQOsPkT7rJfkl83oK1tQQWX7YRSMdec06onmr2wgjAcZYxGzGA4E4czHEtAnGPXqSK1fsIHvj1ld5++LofNKOiVoogVQ7KsktHJEeyKZ4wEOe+QMQCy586e5Z5ih7DRZO9YdX5AJgt54nOV2KzvVL1S7pE1EXQ7BVtN+NmgI5gYK6C6beWldZ4kDDuHr2ZlRiy8XlnyRaveUsAaHZr5hwAFlb9kySJyGEYNzE5dzSiXlUEcnFJLTYf4MgQBu4fA9IAben0gKPgp0+V1YHRgNouUfTjCl7xlqqMAjvG3jGdhPyULV3uwbOrtKy798NmRWKvys9Wk+MnjmFKMZ/p/lUPbWDHLdlb+u5K4tHkY0YKBN6h+X3SPHuT0yp36yxCSnjJdIXI0iupsxUbuh+T8a9lf++ik0TQvILmi5K9Ym8j7BIePzUo7W28q+U5BXvT2KWFudiStpvefMSX2IhjxFSyBRwCIsCBBt7B8eBfI47g7Fr6oZV/wAfp1hIXSaMdwyFRxE/5hlVnbm7Ms="/>
    <input type="hidden" value="14b76fa88f1c3c97b005fb7e18d5389b" id="jschl-vc" name="jschl_vc"/>
    <!-- <input type="hidden" value="" id="jschl-vc" name="jschl_vc"/> -->
    <input type="hidden" name="pass" value="1603731742.874-yi+PgF4NxE"/>
    <input type="hidden" id="jschl-answer" name="jschl_answer"/>
  </form>
```
- Some of these variable will be passed to JS dynamically to generate a url as below -
```text
      https://devza.com/cdn-cgi/challenge-platform/h/g/generate/ov1/0.8121086861055473:1603645414:03d11a3202107c611cce457b50ab30a8c2b5b27edd662c638dc9157f3c8191d4/5e7d8955ac641acc/2bb648d50bfec4d
      <----------- Fixed -----------------------------------------><---- Extracted by loading the dynamically inject js and then parsing the js script ----------><-- Ray ID -----><-- cHash(JS variable) ->
```
- Once cloudflare is done with DDoS check in the browser, Above url will be called two times with some hash (different hash for each request) as below - 
```json
v_{ray_id}: "Some Hash genrated by dynamically injected JS"
```
- The above two request will set the cookies in the browser
- At the end form will be submitted and final value will be returned to the user.

Note: In my case final call is returning 302 instead of actual response.
