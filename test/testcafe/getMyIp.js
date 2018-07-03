module.exports = async function()
{
  return new Promise((resolve, reject) => {
    require('dns').lookup( require('os').hostname(), function (err, ip, fam) {
      if(err) return reject(new Error(err));
      resolve(ip);
    });
  });
}